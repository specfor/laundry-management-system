<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\StockManagement\models\accounting\Accounting;
use LogicLeap\StockManagement\models\API;

class FinancialAccountController extends API
{   
    public function getFinancialAccounts(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $accountId = self::getParameter('account-id', dataType: 'int');
        $name = self::getParameter('account-name');
        $code = self::getParameter('account-code');
        $type = self::getParameter('account-type');
        $taxId = self::getParameter('tax-id', dataType: 'int');
        $description = self::getParameter('description');

        $data = Accounting::getAccounts($pageNumber, $accountId, $name, $code, $type, $description, $taxId);
        self::sendSuccess(['financial-accounts' => $data]);
    }

    public function addFinancialAccount(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_WRITE]]);

        $name = self::getParameter('account-name', isCompulsory: true);
        $code = self::getParameter('account-code', isCompulsory: true);
        $type = self::getParameter('account-type', isCompulsory: true);
        $taxId = self::getParameter('tax-id', dataType: 'int', isCompulsory: true);
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
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_MODIFY]]);

        $accountId = self::getParameter('account-id', dataType: 'int', isCompulsory: true);

        self::sendError('Not implemented yet.');
    }

    public function deleteFinancialAccount(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_DELETE]]);

        $accountId = self::getParameter('account-id', dataType: 'int', isCompulsory: true);

        $status = Accounting::deleteAccount($accountId);
        if (is_string($status))
            self::sendError($status);
        self::sendSuccess('Successfully removed the account.');
    }
}