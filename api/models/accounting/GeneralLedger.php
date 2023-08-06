<?php

namespace LogicLeap\StockManagement\models\accounting;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class GeneralLedger extends DbModel
{
    private const TABLE_NAME = 'general_ledger';

    public static function getLedgerRecords(int    $pageNumber = 0, int $accountId = null, string $reference = null,
                                            string $description = null, bool $isDebit = null, string $amountMin = null,
                                            string $amountMax = null, string $taxMin = null, string $taxMax = null,
                                            int    $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];

        if ($accountId)
            $filters[] = "account_id=$accountId";
        if ($reference) {
            $filters[] = "reference LIKE :reference";
            $placeholders['reference'] = "%" . $reference . "%";
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

    public static function createLedgerRecord(int    $accountId, string $reference = null, string $description = null,
                                              string $credit = null, string $debit = null, bool $taxInclusive = false): string|array
    {
        if (!empty($credit) && !empty($debit))
            return "Can not both credit and debit in a single record.";

        if (empty($credit) && empty($debit))
            return "Can not both credit and debit be empty in a single record.";

        $data = Accounting::getAccounts(accountId: $accountId);
        if (empty($data))
            return "Invalid account ID.";

        $params['account_id'] = $accountId;
        $params['reference'] = $reference;
        $params['description'] = $description;

        $taxDataSql = "select tax_rate from taxes where tax_id=(Select tax_id from 
                                                           financial_accounts where account_id=$accountId)";
        $statement = self::prepare($taxDataSql);
        $statement->execute();
        $taxRate = $statement->fetch(PDO::FETCH_ASSOC)['tax_rate'];

        if ($taxInclusive) {
            if ($credit) {
                $params['credit'] = bcdiv(bcmul($credit, "100"), bcadd($taxRate, "100"));
                $params['tax'] = bcsub($credit, $params['credit']);
            } else {
                $params['debit'] = bcdiv(bcmul($debit, "100"), bcadd($taxRate, "100"));
                $params['tax'] = bcsub($credit, $params['debit']);
            }
        } else {
            if ($credit) {
                $params['credit'] = $credit;
                $params['tax'] = bcmul(bcdiv($credit, "100"), $taxRate);
            } else {
                $params['debit'] = $debit;
                $params['tax'] = bcmul(bcdiv($debit, "100"), $taxRate);
            }
        }

        $params['timestamp'] = time();

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return "Failed to insert data into the database.";
        return ['record_id' => $id];
    }
}