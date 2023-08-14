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

    public static function getTotalsByDate(int $accountId = null, array $dates = null, int $month = null, int $year = null): array
    {
        $filters = [];
        $placeholders = [];

        if ($accountId)
            $filters[] = "account_id=$accountId";
        if ($dates) {
            $fil = [];
            foreach ($dates as $index => $date) {
                $fil[] = "date = :date$index";
                $placeholders["date$index"] = $date;
            }
            $filters[] = '(' . implode(' or ', $fil) . ')';
        }
        if ($month) {
            if ($month < 10)
                $month = "0$month";
            $filters[] = "date LIKE  '%-$month-%'";
        }
        if ($year)
            $filters[] = "date LIKE '$year-%'";

        $condition = implode(' AND ', $filters);

        return self::getDataFromTable(['account_id', 'date', 'credit', 'debit'], self::TABLE_NAME,
            $condition, $placeholders, ['date', 'desc'])->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTotalsByMonth(int $accountId = null, int $month = null, int $year = null): array
    {
        $filters = [];
        $placeholders = [];

        if ($accountId)
            $filters[] = "account_id=$accountId";
        if ($year && $month) {
            if ($month < 10)
                $month = "0$month";
            $filters[] = "date LIKE '$year-$month-%'";
        } elseif ($year) {
            $filters[] = "date LIKE  '$year-%'";
        } elseif ($month) {
            if ($month < 10)
                $month = "0$month";
            $filters[] = "date LIKE  '%-$month-%'";
        }

        $condition = implode(' AND ', $filters);

        $dayRecords = self::getDataFromTable(['account_id', 'date', 'credit', 'debit'], self::TABLE_NAME,
            $condition, $placeholders, ['date', 'desc'])->fetchAll(PDO::FETCH_ASSOC);

        $monthRecords = [];
        foreach ($dayRecords as $dayRecord) {
            $foundInMonthRecords = false;
            foreach ($monthRecords as &$record) {
                if ($record['account_id'] == $dayRecord['account_id'] && $record['date'] == substr($dayRecord['date'], 0, 7)) {
                    $foundInMonthRecords = true;
                    $record['credit'] = bcadd($record['credit'], $dayRecord['credit']);
                    $record['debit'] = bcadd($record['debit'], $dayRecord['debit']);
                    break;
                }

            }
            if (!$foundInMonthRecords) {
                $monthRecords[] = [
                    'account_id' => $dayRecord['account_id'],
                    'date' => substr($dayRecord['date'], 0, 7),
                    'credit' => $dayRecord['credit'],
                    'debit' => $dayRecord['debit']
                ];
            }
        }
        return $monthRecords;
    }

    public static function getTotalsByYear(int $accountId = null, int $year = null): array
    {
        $filters = [];
        $placeholders = [];

        if ($accountId)
            $filters[] = "account_id=$accountId";
        if ($year) {
            $filters[] = "date LIKE '$year-%'";
        }

        $condition = implode(' AND ', $filters);

        $dayRecords = self::getDataFromTable(['account_id', 'date', 'credit', 'debit'], self::TABLE_NAME,
            $condition, $placeholders, ['date', 'desc'])->fetchAll(PDO::FETCH_ASSOC);

        $yearRecords = [];
        foreach ($dayRecords as $dayRecord) {
            $foundInYearRecords = false;
            foreach ($yearRecords as &$record) {
                if ($record['account_id'] == $dayRecord['account_id'] && $record['date'] == substr($dayRecord['date'], 0, 4)) {
                    $foundInYearRecords = true;
                    $record['credit'] = bcadd($record['credit'], $dayRecord['credit']);
                    $record['debit'] = bcadd($record['debit'], $dayRecord['debit']);
                    break;
                }

            }
            if (!$foundInYearRecords) {
                $yearRecords[] = [
                    'account_id' => $dayRecord['account_id'],
                    'date' => substr($dayRecord['date'], 0, 4),
                    'credit' => $dayRecord['credit'],
                    'debit' => $dayRecord['debit']
                ];
            }
        }
        return $yearRecords;
    }
}