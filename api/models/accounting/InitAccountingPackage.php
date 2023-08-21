<?php

namespace LogicLeap\StockManagement\models\accounting;

use LogicLeap\StockManagement\models\DbModel;

class InitAccountingPackage extends DbModel
{
    public static function init(): bool
    {
        $taxExempt = Taxes::getTaxes(taxName: 'tax exempt');
        if (empty($taxExempt))
            $taxId = Taxes::createTax('tax exempt', 0, locked: true)['tax_id'];
        else
            $taxId = $taxExempt[0]['tax_id'];

        $taxAccount = Accounting::getAccounts(name: 'sales tax');
        if (empty($taxAccount))
            Accounting::createAccount('sales tax', 't000', 'Current Liability', $taxId,
                'This is the account used to track sales taxes on income and expenses. This software is designed 
            to use only one sales tax account.', false, true);

        return true;
    }
}