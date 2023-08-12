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
//        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        $accountId = self::getParameter('account-id', dataType: 'int');
        $date = self::getParameter('date');

        AccountTotals::calculateAccountTotals();
        self::sendSuccess(['account-totals' => AccountTotals::getTotalsByDate($accountId, $date)]);
    }

    public function getTotalByMonth(): void
    {
//        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        $accountId = self::getParameter('account-id', dataType: 'int');
        $month = self::getParameter('month');
        $year = self::getParameter('year');

        AccountTotals::calculateAccountTotals();
        self::sendSuccess(['account-totals' => AccountTotals::getTotalsByMonth($accountId, $month, $year)]);
    }
}