<?php

namespace LogicLeap\StockManagement\models\accounting;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class AccountTotals extends DbModel
{
    private const TABLE_NAME = 'financial_account_totals';

    public static function calculateAccountTotals(bool $forceRecalculate = false): void
    {
        $accountTotalsUpdated = false;
        $accountTotals = self::getDataFromTable(['*'], self::TABLE_NAME)->fetchAll(PDO::FETCH_ASSOC);
        $newAccountTotals = [];

        $lastReadLedgerRecord = 0;
        if ($forceRecalculate) {
            foreach ($accountTotals as &$account) {
                $account['credit'] = '0';
                $account['debit'] = '0';
            }
        } else {
            if (!empty($accountTotals))
                $lastReadLedgerRecord = $accountTotals[0]['until_ledger_rec_id'];
        }

        $statement = self::getDataFromTable(['record_id', 'body', 'date'], 'general_ledger',
            "record_id > $lastReadLedgerRecord", orderBy: ['record_id', 'asc']);

        while (true) {
            $ledgerData = $statement->fetch(PDO::FETCH_ASSOC);

            if ($ledgerData === false)
                break;

            $lastReadLedgerRecord = $ledgerData['record_id'];
            $ledgerData['body'] = json_decode($ledgerData['body'], true);

            foreach ($ledgerData['body'] as $ledgerEntry) {
                $accountIndex = -1;
                $newAccountIndex = -1;
                foreach ($accountTotals as $index => $account) {
                    if ($account['account_id'] == $ledgerEntry['account_id'] && $account['date'] == $ledgerData['date']) {
                        $accountIndex = $index;
                        break;
                    }
                }
                if ($accountIndex == -1)
                    foreach ($newAccountTotals as $index => $account) {
                        if ($account['account_id'] == $ledgerEntry['account_id'] && $account['date'] == $ledgerData['date']) {
                            $newAccountIndex = $index;
                            break;
                        }
                    }

                if ($accountIndex == -1 && $newAccountIndex == -1) {
                    echo "adding account. \n";
                    $newAccountTotals[] = [
                        'account_id' => $ledgerEntry['account_id'],
                        'date' => $ledgerData['date'],
                        'credit' => $ledgerEntry['credit'] ?? "0.0000",
                        'debit' => $ledgerEntry['debit'] ?? "0.0000"
                    ];
                } elseif ($accountIndex != -1) {
                    echo "modify account.\n";
                    $accountTotalsUpdated = true;
                    if (isset($ledgerEntry['credit']))
                        $accountTotals[$accountIndex]['credit'] = bcadd($accountTotals[$accountIndex]['credit'],
                            $ledgerEntry['credit'] ?? "0.0000");
                    else
                        $accountTotals[$accountIndex]['debit'] = bcadd($accountTotals[$accountIndex]['debit'],
                            $ledgerEntry['debit'] ?? "0.0000");
                } elseif ($newAccountIndex != -1) {
                    echo "modify new account.\n";
                    if (isset($ledgerEntry['credit']))
                        $newAccountTotals[$newAccountIndex]['credit'] = bcadd($newAccountTotals[$newAccountIndex]['credit'],
                            $ledgerEntry['credit']);
                    else
                        $newAccountTotals[$newAccountIndex]['debit'] = bcadd($newAccountTotals[$newAccountIndex]['debit'],
                            $ledgerEntry['debit']);
                }
            }
        }

        if ($accountTotalsUpdated)
            foreach ($accountTotals as &$account) {
                $account['until_ledger_rec_id'] = $lastReadLedgerRecord;
                $accountId = $account['account_id'];
                unset($account['account_id']);

                self::updateTableData(self::TABLE_NAME, $account, "account_id=$accountId AND date=" . $account['date']);
            }

//        var_dump($newAccountTotals);
        if (!empty($newAccountTotals))
            foreach ($newAccountTotals as &$account) {
                $account['until_ledger_rec_id'] = $lastReadLedgerRecord;
                try {

                    self::insertIntoTable(self::TABLE_NAME, $account);
                } catch (\Exception $e) {

                    var_dump($e->getMessage());
                }
            }
    }

    public static function getTotalsByDate(int $accountId = null, string $date = null): array
    {
        $filters = [];
        $placeholders = [];

        if ($accountId)
            $filters[] = "account_id=$accountId";
        if ($date) {
            $filters[] = "date = :date";
            $placeholders['date'] = $date;
        }

        $condition = implode(' AND ', $filters);

        return self::getDataFromTable(['account_id', 'date', 'credit', 'debit'], self::TABLE_NAME,
            $condition, $placeholders, ['date', 'desc'])->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalsByMonth(int $accountId = null, string $month = null, string $year = null): array
    {

    }
}