<?php

namespace LogicLeap\StockManagement\models\accounting;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class AccountTotals extends DbModel
{
    private const TABLE_NAME = 'financial_account_totals';

    public static function calculateAccountTotals(bool $forceRecalculate = false): array
    {
        $accountTotals = self::getDataFromTable(['*'], self::TABLE_NAME)->fetchAll(PDO::FETCH_ASSOC);

        $lastReadLedgerRecord = 0;
        if ($forceRecalculate) {
            foreach ($accountTotals as &$account) {
                $account['credit'] = '0';
                $account['debit'] = '0';
            }
        } else {
            foreach ($accountTotals as $account) {
                // This is done because we set 'until_ledger_rec_id' to 0 when creating new account.
                if ($account['until_ledger_rec_id'] > $lastReadLedgerRecord)
                    $lastReadLedgerRecord = $account['until_ledger_rec_id'];
            }
        }

        $statement = self::getDataFromTable(['record_id', 'account_id', 'credit', 'debit'], 'general_ledger',
            "record_id > $lastReadLedgerRecord");

        while (true) {
            $data = $statement->fetch(PDO::FETCH_ASSOC);

            if ($data === false)
                break;

            foreach ($accountTotals as &$account) {
                if ($account['account_id'] == $data['account_id']) {
                    if (!empty($data['credit']))
                        $account['credit'] = bcadd($account['credit'], $data['credit']);
                    else
                        $account['debit'] = bcadd($account['debit'], $data['debit']);
                    break;
                }
                if ($lastReadLedgerRecord < $data['record_id'])
                    $lastReadLedgerRecord = $data['record_id'];
            }
        }
        foreach ($accountTotals as &$account) {
            $account['until_ledger_rec_id'] = $lastReadLedgerRecord;
            $accountId = $account['account_id'];
            unset($account['account_id']);

            self::updateTableData(self::TABLE_NAME, $account, "account_id=$accountId");

            unset($account['until_ledger_rec_id']);
            $account['account_id'] = $accountId;
        }
        return $accountTotals;
    }

    public static function createTableRowForNewAccount(int $accountId): bool
    {
        $params['account_id'] = $accountId;
        $params['credit'] = 0;
        $params['debit'] = 0;
        $params['until_ledger_rec_id'] = 0;
        if (self::insertIntoTable(self::TABLE_NAME, $params))
            return true;
        return false;
    }
}