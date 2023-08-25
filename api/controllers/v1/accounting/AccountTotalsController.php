<?php

namespace LogicLeap\StockManagement\controllers\v1\accounting;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\accounting\AccountTotals;
use LogicLeap\StockManagement\models\user_management\User;

class AccountTotalsController extends Controller
{
    public function calculateTotals(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        $forceRecalculate = self::getParameter('force-recalculate', defaultValue: false, dataType: 'bool');
        AccountTotals::calculateAccountTotals($forceRecalculate);

        self::sendSuccess("Successfully calculated the totals.");
    }

    public function getTotalByDate(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        $accountId = self::getParameter('account-id', dataType: 'int');
        $dates = self::getParameter('dates');
        $month = self::getParameter('month', dataType: 'int');
        $year = self::getParameter('year', dataType: 'int');

        if (empty($dates))
            $dates = null;
        else
            $dates = explode(',', $dates);

        AccountTotals::calculateAccountTotals();
        [$totals, $count] = AccountTotals::getTotalsByDate($accountId, $dates, $month, $year);
        self::sendSuccess(['account-totals' => $totals, 'record_count' => $count]);
    }

    public function getTotalByMonth(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        $accountId = self::getParameter('account-id', dataType: 'int');
        $month = self::getParameter('month', dataType: 'int');
        $year = self::getParameter('year', dataType: 'int');

        AccountTotals::calculateAccountTotals();
        [$totals, $count] = AccountTotals::getTotalsByMonth($accountId, $month, $year);
        self::sendSuccess(['account-totals' => $totals, 'record_count' => $count]);
    }

    public function getTotalByYear(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        $accountId = self::getParameter('account-id', dataType: 'int');
        $year = self::getParameter('year', dataType: 'int');

        AccountTotals::calculateAccountTotals();
        [$totals, $count] = AccountTotals::getTotalsByYear($accountId, $year);
        self::sendSuccess(['account-totals' => $totals, 'record_count' => $count]);
    }

}