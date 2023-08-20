<?php

namespace LogicLeap\StockManagement\controllers\v1\accounting;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\accounting\Accounting;
use LogicLeap\StockManagement\models\user_management\User;

class FinancialAccountController extends Controller
{
    public function getFinancialAccounts(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $accountId = self::getParameter('account_id', dataType: 'int');
        $name = self::getParameter('account-name');
        $code = self::getParameter('account-code');
        $type = self::getParameter('account-type');
        $taxId = self::getParameter('tax_id', dataType: 'int');
        $description = self::getParameter('description');
        $limit = self::getParameter('limit', defaultValue: 30, dataType: 'int');

        $data = Accounting::getAccounts($pageNumber, $accountId, $name, $code, $type, $description, $taxId, $limit);
        self::sendSuccess(['financial-accounts' => $data]);
    }

    public function addFinancialAccount(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_WRITE]]);

        $name = self::getParameter('name', isCompulsory: true);
        $code = self::getParameter('code', isCompulsory: true);
        $type = self::getParameter('type', isCompulsory: true);
        $taxId = self::getParameter('tax_id', dataType: 'int', isCompulsory: true);
        $description = self::getParameter('description');

        $status = Accounting::createAccount($name, $code, $type, $taxId, $description);
        if (is_string($status))
            self::sendError($status);
        else {
            $status['message'] = "Successfully created the new account.";
            self::sendSuccess($status);
        }
    }

    public function updateFinancialAccount(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_MODIFY]]);

        $accountId = self::getParameter('account_id', dataType: 'int', isCompulsory: true);
        $name = self::getParameter('name');
        $taxId = self::getParameter('tax_id', dataType: 'int');
        $desc = self::getParameter('description');

        $status = Accounting::updateAccount($accountId, $name, $taxId, $desc);
        if (is_string($status))
            self::sendError($status);
        else
            self::sendSuccess("Successfully updated the account.");
    }

    public function deleteFinancialAccount(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_DELETE]]);

        $accountId = self::getParameter('account_id', dataType: 'int', isCompulsory: true);

        $status = Accounting::deleteAccount($accountId);
        if (is_string($status))
            self::sendError($status);
        self::sendSuccess('Successfully removed the account.');
    }

    public function getAccountTypes(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        self::sendSuccess(['account-types' => Accounting::getAccountTypes()]);
    }
}