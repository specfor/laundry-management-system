<?php

namespace LogicLeap\StockManagement\controllers\v1\accounting;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\accounting\GeneralLedger;
use LogicLeap\StockManagement\models\user_management\User;

class LedgerRecordController extends Controller
{
    public function getLedgerRecords(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_READ]]);

        $recordId = self::getParameter('record_id', dataType: 'int');
        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $narration = self::getParameter('narration');
        $date = self::getParameter('date');
        $limit = self::getParameter('limit', defaultValue: 30, dataType: 'int');

        [$data, $count] = GeneralLedger::getLedgerRecords($pageNumber, $recordId, $narration, $date, $limit);
        self::sendSuccess(['records' => $data, 'record_count' => $count]);
    }

    public function addLedgerRecord(): void
    {
        self::checkPermissions(['financial_accounts' => [User::PERMISSION_WRITE]]);

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