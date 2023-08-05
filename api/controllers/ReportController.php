<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\PhpServerCore\Reports;
use LogicLeap\StockManagement\models\API;

class ReportController extends API
{   
    public function getReport(): void
    {
        (new Reports())->generatePdf();
    }
}