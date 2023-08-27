<?php

namespace LogicLeap\StockManagement\models\accounting;

use DateTime;
use Exception;
use LogicLeap\PhpServerCore\data_types\Decimal;
use LogicLeap\StockManagement\models\DbModel;
use LogicLeap\StockManagement\Util\Util;
use PDO;

class GeneralLedger extends DbModel
{
    private const TABLE_NAME = 'general_ledger';

    private const TAX_TYPE_NO_TAX = 0;
    private const TAX_TYPE_TAX_INCLUSIVE = 1;
    private const TAX_TYPE_TAX_EXCLUSIVE = 2;

    public static function getLedgerRecords(int $pageNumber = 0, int $recordId = null, string $narration = null, string $date = null,
                                            int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];

        if ($recordId) {
            $filters[] = "record_id = :recordId";
            $placeholders['recordId'] = "" . $recordId;
        }
        if ($narration) {
            $filters[] = "narration LIKE :narration";
            $placeholders['narration'] = "%" . $narration . "%";
        }
        if ($date) {
            $filters[] = "date = :date";
            $placeholders['date'] = $date;
        }

        $condition = implode(' AND ', $filters);

        $data = self::getDataFromTable(['*'], self::TABLE_NAME, $condition, $placeholders,
            ['record_id', 'desc'], [$startingIndex, $limit])->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($data)) {
            foreach ($data as &$record) {
                if ($record['tax_type'] === self::TAX_TYPE_NO_TAX)
                    $record['tax_type'] = 'no tax';
                elseif ($record['tax_type'] === self::TAX_TYPE_TAX_INCLUSIVE)
                    $record['tax_type'] = 'tax inclusive';
                elseif ($record['tax_type'] === self::TAX_TYPE_TAX_EXCLUSIVE)
                    $record['tax_type'] = 'tax exclusive';
                $record['body'] = json_decode($record['body'], true);
            }
            unset($record);
        }

        if ($recordId && !empty($data)) {
            foreach ($data[0]['body'] as &$record) {
                if (isset($record['tax_id'])) {
                    $taxData = Taxes::getTaxes(taxId: $record['tax_id'])['taxes'][0];
                    $record['tax_name'] = $taxData['name'];
                    $record['tax_rate'] = $taxData['tax_rate'];
                }
                $accountData = Accounting::getAccounts(accountId: $record['account_id'])['accounts'][0];
                $record['account_name'] = $accountData['name'];
                $record['account_tax_id'] = $accountData['tax_id'];
                $record['account_description'] = $accountData['description'];
            }
            unset($record);
        }

        $count = self::countTableRows(self::TABLE_NAME, $condition, $placeholders);

        return ['records' => $data, 'record_count' => $count];
    }

    public static function createLedgerRecord(string $narration, array $body, string $taxType = 'tax exclusive',
                                              string $date = null): string|array
    {
        if (empty($narration))
            return "Narration can not be empty.";

        if ($date) {
            try {
                $date = (new DateTime($date))->format("Y-m-d");
            } catch (Exception $e) {
                return "Invalid date.";
            }
        } else
            $date = (new DateTime('now'))->format('Y-m-d');

        $taxType = strtolower($taxType);
        if ($taxType !== 'no tax' && $taxType !== 'tax inclusive' && $taxType !== 'tax exclusive')
            return "'tax-type' should be one of 'no tax', 'tax inclusive' or 'tax exclusive'";

        // Following variables must be strings as BC Maths lib only accept numbers as strings.
        $totalCredit = new Decimal("0");
        $totalDebit = new Decimal("0");

        // Doing basic data type and data structure validations.
        foreach ($body as &$record) {
            if (!isset($record['account_id'], $record['description']) &&
                (!isset($record['credit']) || !isset($record['debit'])))
                return "Each record in body must contain 'account_id', 'description' and 'credit' or 'debit'";

            if (isset($record['debit'], $record['credit']))
                return "Can not 'credit' and 'debit' at same time.";

            $record['account_id'] = Util::getConvertedTo($record['account_id'], 'int');
            if ($record['account_id'] === null)
                return "'account_id' '" . $record['account_id'] . "' is not integer.";

            if (isset($record['tax_id'])) {
                $record['tax_id'] = Util::getConvertedTo($record['tax_id'], 'int');
                if ($record['tax_id'] === null)
                    return "'tax_id' '" . $record['tax_id'] . "' is not integer.";
            }

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

        $taxAccountId = Accounting::getAccounts(name: 'sales tax')['accounts'][0]['account_id'];
        $taxRecords = [];

        // Doing all the calculations.
        foreach ($body as &$record) {
            $accountData = Accounting::getAccounts(accountId: $record['account_id'])['accounts'];
            if (empty($accountData))
                return $record['account_id'] . " is an invalid account ID.";

            if ($taxType !== 'no tax') {
                if (isset($record['tax_id']))
                    $taxRate = new Decimal(Taxes::getTaxes(taxId: $record['tax_id'])['taxes'][0]['tax_rate']);
                else
                    $taxRate = new Decimal(Taxes::getTaxes(taxId: $accountData[0]['tax_id'])['taxes'][0]['tax_rate']);

                $params['tax_type'] = self::TAX_TYPE_TAX_EXCLUSIVE;
                if ($taxType === 'tax inclusive') {
                    if (isset($record['credit'])) {
                        $credit = $record['credit'];
                        $record['credit'] = $record['credit']->mul(new Decimal('100'))->div($taxRate->add(new Decimal('100')));
                        $tax = $credit->sub($record['credit']);
                    } else {
                        $debit = $record['debit'];
                        $record['debit'] = $record['debit']->mul(new Decimal('100'))->div($taxRate->add(new Decimal('100')));
                        $tax = $debit->sub($record['debit']);
                    }
                } else {
                    if (isset($record['credit'])) {
                        $tax = $record['credit']->div(new Decimal("100"))->mul($taxRate);
                    } else {
                        $tax = $record['debit']->div(new Decimal("100"))->mul($taxRate);
                    }
                }
                if ($tax->getDecimal() !== '0.0000')
                    if (isset($record['credit'])) {
                        $taxRecords[] = ['account_id' => $taxAccountId, 'credit' => $tax->getDecimal(), 'description' => ''];
                        $totalCredit = $totalCredit->add($tax);
                    } else {
                        $taxRecords[] = ['account_id' => $taxAccountId, 'debit' => $tax->getDecimal(), 'description' => ''];
                        $totalDebit = $totalDebit->add($tax);
                    }
            } else
                $params['tax_type'] = self::TAX_TYPE_NO_TAX;

            if (isset($record['credit'])) {
                $totalCredit = $totalCredit->add($record['credit']);
                $record['credit'] = $record['credit']->getDecimal();
            } else {
                $totalDebit = $totalDebit->add($record['debit']);
                $record['debit'] = $record['debit']->getDecimal();
            }
        }

        if ($taxType !== 'no tax')
            $body = array_merge($body, $taxRecords);

        if ($totalDebit->getDecimal() !== $totalCredit->getDecimal())
            return "Total credits must always be equal to total debits.";

        $params['narration'] = $narration;
        $params['created_at'] = time();
        $params['body'] = json_encode($body);
        $params['tot_amount'] = $totalCredit;
        $params['date'] = $date;

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return "Failed to insert data into the database.";
        return ['record_id' => $id];
    }
}