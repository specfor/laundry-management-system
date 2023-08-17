<?php

namespace LogicLeap\StockManagement\controllers\v1\stock_management;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\stock_management\Customers;
use LogicLeap\StockManagement\models\user_management\User;

class CustomerController extends Controller
{   
    public function addCustomer(): void
    {
        self::checkPermissions(['customers' => [User::PERMISSION_WRITE]]);

        $customerName = self::getParameter('customer-name', isCompulsory: true);
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $address = self::getParameter('address');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        $status = Customers::addNewCustomer($customerName, $email, $phoneNumber, $address, $branchId);
        if (is_array($status))
            self::sendSuccess(['message' => 'New customer was added successfully.', 'customer-id' => $status['customer_id']]);
        else
            self::sendError('Failed to add new customer');
    }

    public function getCustomers(): void
    {
        self::checkPermissions(['customers' => User::PERMISSION_READ]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $customerId = self::getParameter('customer-id', dataType: 'int');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $email = self::getParameter('email');
        $phoneNum = self::getParameter('phone-number');
        $name = self::getParameter('name');
        $address = self::getParameter('address');
        $banned = self::getParameter('banned', dataType: 'bool');
        $joinDate = self::getParameter('join-date');

        if ($branchId === null)
            $branchId = User::getUserBranchId(self::getUserId());

        $data = Customers::getCustomers($customerId, $branchId, $email, $phoneNum, $name, $address, $banned, $joinDate, $pageNum);
        self::sendSuccess(['customers' => $data]);
    }

    public function updateCustomer(): void
    {
        self::checkPermissions(['customers' => [User::PERMISSION_MODIFY]]);

        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        $email = self::getParameter('email');
        $customerName = self::getParameter('customer-name');
        $phoneNumber = self::getParameter('phone-number');
        $address = self::getParameter('address');
        $banned = self::getParameter('banned', dataType: 'bool');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        if (Customers::updateCustomer($customerId, $customerName, $email, $phoneNumber, $address, $branchId, $banned))
            self::sendSuccess('Customer details were updated successfully.');
        else
            self::sendError('Failed to update customer details.');
    }

    public function deleteCustomer(): void
    {
        self::checkPermissions(['customers' => [User::PERMISSION_DELETE]]);

        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        if (Customers::deleteCustomer($customerId))
            self::sendSuccess('Successfully deleted the customer.');
        else
            self::sendError('Failed to delete the customer');
    }
}