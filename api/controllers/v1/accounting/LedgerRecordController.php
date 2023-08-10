<?php

namespace LogicLeap\StockManagement\controllers\v1\accounting;

use LogicLeap\StockManagement\controllers\v1\Controller;
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

        $narration = self::getParameter('narration', isCompulsory: true);
        $body = self::getParameter('body', dataType: 'array', isCompulsory: true);
        $taxType = self::getParameter('tax-type', isCompulsory: true);
        $date = self::getParameter('date');

        $status = GeneralLedger::createLedgerRecord($narration, $body, $taxType, $date);
        if (is_string($status))
            self::sendError($status);
        else {
            $status['message'] = "Successfully created the new ledger record.";
            self::sendSuccess($status);
        }
    }
}