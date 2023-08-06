<?php

namespace LogicLeap\StockManagement\controllers\v1\accounting;

use LogicLeap\PhpServerCore\Controller;
use LogicLeap\StockManagement\models\accounting\GeneralLedger;

class LedgerRecordController extends Controller
{   
    public function getLedgerRecords(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $accountId = self::getParameter('account-id', dataType: 'int');
        $reference = self::getParameter('reference');
        $description = self::getParameter('description');
        $isDebit = self::getParameter('is-debit', dataType: 'bool');
        $amountMin = self::getParameter('amount-min', dataType: 'decimal');
        $amountMax = self::getParameter('amount-max', dataType: 'decimal');
        $taxMin = self::getParameter('tax-min', dataType: 'decimal');
        $taxMax = self::getParameter('tax-max', dataType: 'decimal');

        $data = GeneralLedger::getLedgerRecords($pageNumber, $accountId, $reference, $description, $isDebit, $amountMin,
            $amountMax, $taxMin, $taxMax);
        self::sendSuccess(['records' => $data]);
    }

    public function addLedgerRecord(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_WRITE]]);

        $accountId = self::getParameter('account-id', dataType: 'int', isCompulsory: true);
        $reference = self::getParameter('reference');
        $description = self::getParameter('description');
        $credit = self::getParameter('credit', dataType: 'decimal');
        $debit = self::getParameter('debit', dataType: 'decimal');
        $tax = self::getParameter('tax', dataType: 'decimal');

        $status = GeneralLedger::createLedgerRecord($accountId, $reference, $description, $credit, $debit, $tax);
        if (is_string($status))
            self::sendError($status);
        else {
            $status['message'] = "Successfully created the new ledger record.";
            self::sendSuccess($status);
        }
    }
}