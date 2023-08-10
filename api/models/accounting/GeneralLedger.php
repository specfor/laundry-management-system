<?php

namespace LogicLeap\StockManagement\models\accounting;

use DateTime;
use LogicLeap\StockManagement\models\DbModel;
use LogicLeap\StockManagement\Util\Util;
use PDO;

class GeneralLedger extends DbModel
{
    private const TABLE_NAME = 'general_ledger';

    public static function getLedgerRecords(int    $pageNumber = 0, string $narration = null, string $date = null,
                                            string $description = null, bool $isDebit = null, string $amountMin = null,
                                            string $amountMax = null, string $taxMin = null, string $taxMax = null,
                                            int    $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];

        if ($narration) {
            $filters[] = "$narration LIKE :narration";
            $placeholders['narration'] = "%" . $narration . "%";
        }
        if ($description) {
            $filters[] = "description LIKE :desc";
            $placeholders['desc'] = "%$description%";
        }
        if ($isDebit || $amountMax || $amountMin) {
            if ($isDebit === null)
                $type = '';
            $filters[] = "debit < :amountMax AND debit > :amountMin";
            $placeholders['amountMax'] = $amountMax;
            $placeholders['amountMin'] = $amountMin;
        }
        if ($taxMax) {
            $filters[] = "tax < :max";
            $placeholders['max'] = $taxMax;
        }
        if ($taxMin) {
            $filters[] = "tax > :min";
            $placeholders['min'] = $taxMin;
        }

        $condition = implode(' AND ', $filters);
        $data = self::getDataFromTable(['*'], self::TABLE_NAME, $condition, $placeholders,
            ['record_id', 'desc'], [$startingIndex, $limit])->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public static function createLedgerRecord(string $narration, array $body, string $date = null): string|array
    {
        if (empty($narration))
            return "Narration can not be empty.";

        // Following variables must be strings as BC Maths lib only accept numbers as strings.
        $totalCredit = "0";
        $totalDebit = "0";

        // Doing basic data type and data structure validations.
        foreach ($body as &$record) {
            if (!isset($record['account_id'], $record['description'], $record['tax_inclusive']) &&
                (!isset($record['credit']) || !isset($record['debit'])))
                return "Each record in body must contain 'account_id','description', 'tax_inclusive' and 'credit' or 'debit'";

            if (isset($record['debit'], $record['credit']))
                return "'credit' and 'debit' can not be in a single record.";

            $record['account_id'] = Util::getConvertedTo($record['account_id'], 'int');
            if ($record['account_id'] === null)
                return "'account_id' '" . $record['account_id'] . "' is not integer.";

            $record['tax_inclusive'] = Util::getConvertedTo($record['tax_inclusive'], 'bool');
            if ($record['tax_inclusive'] === null)
                return "'tax_inclusive' '" . $record['tax_inclusive'] . "' is not boolean.";

            if (isset($record['credit'])) {
                $record['credit'] = Util::getConvertedTo($record['credit'], 'decimal');
                if ($record['credit'] === null)
                    return "'credit' '" . $record['credit'] . "' is not decimal.";
            } else {
                $record['debit'] = Util::getConvertedTo($record['debit'], 'decimal');
                if ($record['debit'] === null)
                    return "'debit' '" . $record['debit'] . "' is not decimal.";
            }
        }

        $taxAccountId = Accounting::getAccounts(name: 'sales tax')[0]['account_id'];
        $taxRecords = [];

        // Doing all the calculations.
        foreach ($body as &$record) {
            $accountData = Accounting::getAccounts(accountId: $record['account_id']);
            if (empty($accountData))
                return $record['account_id'] . " is an invalid account ID.";

            $taxRate = Taxes::getTaxes(taxId: $accountData[0]['tax_id'])[0]['tax_rate'];

            if ($record['tax_inclusive']) {
                if (isset($record['credit'])) {
                    $credit = $record['credit'];
                    $record['credit'] = bcdiv(bcmul($record['credit'], "100"), bcadd($taxRate, "100"));
                    $tax = bcsub($credit, $record['credit']);
                } else {
                    $debit = $record['debit'];
                    $record['debit'] = bcdiv(bcmul($record['debit'], "100"), bcadd($taxRate, "100"));
                    $tax = bcsub($debit, $record['debit']);
                }
            } else {
                if (isset($record['credit'])) {
                    $tax = bcmul(bcdiv($record['credit'], "100"), $taxRate);
                } else {
                    $tax = bcmul(bcdiv($record['debit'], "100"), $taxRate);
                }
            }

            if (isset($record['credit'])) {
                $taxRecords[] = ['account_id' => $taxAccountId, 'credit' => $tax, 'description' => ''];
                $totalCredit = bcadd($totalCredit, $record['credit']);
                $totalCredit = bcadd($totalCredit, $tax);
            } else {
                $taxRecords[] = ['account_id' => $taxAccountId, 'debit' => $tax, 'description' => ''];
                $totalDebit = bcadd($totalDebit, $record['debit']);
                $totalDebit = bcadd($totalDebit, $tax);
            }
        }

        $body = array_merge($body, $taxRecords);

        if ($totalDebit !== $totalCredit)
            return "Total credits must always be equal to total debits.";

        $params['narration'] = $narration;
        $params['body'] = json_encode($body);
        $params['tot_amount'] = $totalCredit;
        $params['date'] = $date ?? (new DateTime('now'))->format('Y-m-d');

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return "Failed to insert data into the database.";
        return ['record_id' => $id];
    }
}