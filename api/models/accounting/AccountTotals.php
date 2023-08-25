<?php

namespace LogicLeap\StockManagement\models\accounting;

use LogicLeap\PhpServerCore\data_types\Decimal;
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

        foreach ($accountTotals as &$account) {
            $account['credit'] = new Decimal($account['credit']);
            $account['debit'] = new Decimal($account['debit']);
        }

        $lastReadLedgerRecord = 0;
        if ($forceRecalculate) {
            foreach ($accountTotals as &$account) {
                $account['credit'] = new Decimal('0');
                $account['debit'] = new Decimal('0');
            }
        } else {
            if (!empty($accountTotals))
                $lastReadLedgerRecord = $accountTotals[0]['until_ledger_rec_id'];
        }
        unset($account);

        $statement = self::getDataFromTable(['record_id', 'body', 'date'], 'general_ledger',
            "record_id > $lastReadLedgerRecord", orderBy: ['record_id', 'asc']);

        while (true) {
            $ledgerData = $statement->fetch(PDO::FETCH_ASSOC);

            if ($ledgerData === false)
                break;

            $lastReadLedgerRecord = $ledgerData['record_id'];
            $ledgerData['body'] = json_decode($ledgerData['body'], true);

            foreach ($ledgerData['body'] as $ledgerEntry) {
                $accountArrayKey = null;
                $newAccountArrayKey = null;

                foreach ($accountTotals as $key => $account) {
                    if ($account['account_id'] == $ledgerEntry['account_id'] && $account['date'] == $ledgerData['date']) {
                        $accountArrayKey = $key;
                        break;
                    }
                }
                if ($accountArrayKey === null) {
                    foreach ($newAccountTotals as $key => $newAccount) {
                        if ($newAccount['account_id'] == $ledgerEntry['account_id'] && $newAccount['date'] == $ledgerData['date']) {
                            $newAccountArrayKey = $key;
                            break;
                        }
                    }
                }

                if ($accountArrayKey === null && $newAccountArrayKey === null) {
                    $newAccountTotals[] = [
                        'account_id' => $ledgerEntry['account_id'],
                        'date' => $ledgerData['date'],
                        'credit' => new Decimal($ledgerEntry['credit'] ?? "0.0000"),
                        'debit' => new Decimal($ledgerEntry['debit'] ?? "0.0000")
                    ];
                } elseif ($accountArrayKey !== null) {
                    $accountTotalsUpdated = true;
                    $accountTotals[$accountArrayKey]['updated'] = true;
                    if (isset($ledgerEntry['credit']))
                        $accountTotals[$accountArrayKey]['credit'] = $accountTotals[$accountArrayKey]['credit']
                            ->add(new Decimal($ledgerEntry['credit']));
                    else
                        $accountTotals[$accountArrayKey]['debit'] = $accountTotals[$accountArrayKey]['debit']
                            ->add(new Decimal($ledgerEntry['debit']));
                } elseif ($newAccountArrayKey !== null) {
                    if (isset($ledgerEntry['credit']))
                        $newAccountTotals[$newAccountArrayKey]['credit'] = $newAccountTotals[$newAccountArrayKey]['credit']
                            ->add(new Decimal($ledgerEntry['credit']));
                    else
                        $newAccountTotals[$newAccountArrayKey]['debit'] = $newAccountTotals[$newAccountArrayKey]['debit']
                            ->add(new Decimal($ledgerEntry['debit']));
                }
            }
        }

        if ($accountTotalsUpdated) {
            foreach ($accountTotals as &$account) {
                if (isset($account['updated'])) {
                    $account['until_ledger_rec_id'] = $lastReadLedgerRecord;
                    $accountId = $account['account_id'];
                    $account['credit'] = $account['credit']->getDecimal();
                    $account['debit'] = $account['debit']->getDecimal();
                    unset($account['account_id']);
                    unset($account['updated']);

                    self::updateTableData(self::TABLE_NAME, $account, "account_id=$accountId AND date=" . $account['date']);
                }
            }
            unset($account);
        }

        if (!empty($newAccountTotals))
            foreach ($newAccountTotals as &$account) {
                $account['until_ledger_rec_id'] = $lastReadLedgerRecord;
                $account['credit'] = $account['credit']->getDecimal();
                $account['debit'] = $account['debit']->getDecimal();

                self::insertIntoTable(self::TABLE_NAME, $account);
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

        $totals = self::getDataFromTable(['account_id', 'date', 'credit', 'debit'], self::TABLE_NAME,
            $condition, $placeholders, ['date', 'desc'])->fetchAll(PDO::FETCH_ASSOC);
        $count = self::countTableRows(self::TABLE_NAME, $condition, $placeholders);
        return [$totals, $count];
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
        $count = count($monthRecords);
        return [$monthRecords, $count];
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
        $count = count($yearRecords);
        return [$yearRecords, $count];
    }
}