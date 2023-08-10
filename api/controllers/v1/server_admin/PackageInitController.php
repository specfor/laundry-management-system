<?php

namespace LogicLeap\StockManagement\controllers\v1\server_admin;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\accounting\InitAccountingPackage;

class PackageInitController extends Controller
{
    public function intAccountingPackage(): void
    {
//        self::checkPermissions(onlyServerAdmins: true);

        if (InitAccountingPackage::init())
            self::sendSuccess("Successfully initialized the 'Accounting' package.");
        else
            self::sendError("Failed to initialize the 'Accounting' package.");
    }
}