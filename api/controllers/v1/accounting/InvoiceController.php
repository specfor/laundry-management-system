<?php

namespace LogicLeap\StockManagement\controllers\v1\accounting;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\accounting\Invoices;

class InvoiceController extends Controller
{
    public function sendInvoice()
    {
        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        $products = self::getParameter('products', dataType: 'array', isCompulsory: true);
        
        Invoices::sendInvoice();
//        self::sendSuccess('bla');
    }
}