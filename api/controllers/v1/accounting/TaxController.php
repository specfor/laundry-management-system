<?php

namespace LogicLeap\StockManagement\controllers\v1\accounting;

use LogicLeap\PhpServerCore\Controller;
use LogicLeap\StockManagement\models\accounting\Taxes;
use LogicLeap\StockManagement\models\user_management\User;

class TaxController extends Controller
{
    public function addTax(): void
    {
        self::checkPermissions(['tax' => [User::PERMISSION_WRITE]]);

        $name = self::getParameter('tax-name', isCompulsory: true);
        $description = self::getParameter('description');
        $rate = self::getParameter('tax-rate', dataType: 'float', isCompulsory: true);

        $status = Taxes::createTax($name, $rate, $description);
        if (is_array($status)) {
            $status['message'] = "Successfully created the new tax.";
            self::sendSuccess($status);
        } else
            self::sendError($status);
    }

    public function getTaxes(): void
    {
        self::checkPermissions(['tax' => [User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $taxId = self::getParameter('tax-id', dataType: 'int');
        $name = self::getParameter('tax-name');
        $description = self::getParameter('description');
        $rateMin = self::getParameter('rate-min', dataType: 'float');
        $rateMax = self::getParameter('rate-max', dataType: 'float');

        $data = Taxes::getTaxes($pageNumber, $taxId, $name, $description, $rateMin, $rateMax);
        self::sendSuccess(['taxes' => $data]);
    }

    public function updateTax(): void
    {
        self::checkPermissions(['tax' => [User::PERMISSION_MODIFY]]);

        $taxId = self::getParameter('tax-id', dataType: 'int', isCompulsory: true);
        $name = self::getParameter('tax-name');
        $description = self::getParameter('description');
        $rate = self::getParameter('tax-rate', dataType: 'float');

        $status = Taxes::updateTax($taxId, $name, $description, $rate);
        if ($status === true)
            self::sendSuccess('Tax details were updated successfully.');
        elseif ($status === false)
            self::sendError('Failed to update details on the database.');
        else
            self::sendError($status);
    }

    public function deleteTax(): void
    {
        self::checkPermissions(['tax' => [User::PERMISSION_DELETE]]);

        $taxId = self::getParameter('tax-id', dataType: 'int', isCompulsory: true);

        $status = Taxes::deleteTax($taxId);
        if (is_string($status))
            self::sendError($status);
        elseif ($status)
            self::sendSuccess('Tax details were removed successfully.');
        else
            self::sendError('Failed to remove the tax details.');
    }
}
