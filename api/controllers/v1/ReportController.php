<?php

namespace LogicLeap\StockManagement\controllers\v1;

use LogicLeap\PhpServerCore\Reports;
use LogicLeap\PhpServerCore\Controller;

class ReportController extends Controller
{   
    public function getReport(): void
    {
        (new Reports())->generatePdf();
    }
}