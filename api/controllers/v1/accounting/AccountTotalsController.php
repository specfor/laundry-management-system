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

        self::sendSuccess(['account-totals' => AccountTotals::calculateAccountTotals($forceRecalculate)]);
    }
}