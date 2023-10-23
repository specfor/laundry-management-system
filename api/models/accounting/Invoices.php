<?php

namespace LogicLeap\StockManagement\models\accounting;

use LogicLeap\PhpServerCore\TemplateEngine;

class Invoices
{
    public static function sendInvoice()
    {

        echo TemplateEngine::generateTemplate('invoice.html', TemplateEngine::TEMPLATE_ACCOUNTING, ['name' => 'Amal']);
    }
}