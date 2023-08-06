<?php

namespace LogicLeap\StockManagement\controllers\v1\stock_management;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\stock_management\Employees;
use LogicLeap\StockManagement\models\user_management\User;

class EmployeeController extends Controller
{   
    public function addEmployee(): void
    {
        self::checkPermissions(['employees' => [User::PERMISSION_WRITE]]);

        $name = self::getParameter('employee-name', isCompulsory: true);
        $address = self::getParameter('address');
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $joinDate = self::getParameter('join-date');
        $leftDate = self::getParameter('left-date');

        $status = Employees::addEmployee($name, $address, $email, $phoneNumber, $branchId, $joinDate, $leftDate);
        if (is_array($status))
            self::sendSuccess(['message' => 'New employee was added successfully.', 'employee_id' => $status['employee_id']]);
        else
            self::sendError('Failed to add new employee.');
    }

    public function getEmployees(): void
    {
        self::checkPermissions(['employees' => [User::PERMISSION_READ]]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $name = self::getParameter('name');
        $address = self::getParameter('address');
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $isLeft = self::getParameter('is-left', dataType: 'int');

        $data = Employees::getEmployees($pageNum, $name, $address, $email, $phoneNumber, $branchId, $isLeft);
        self::sendSuccess(['employees' => $data]);
    }

    public function updateEmployee(): void
    {
        self::checkPermissions(['employees' => [User::PERMISSION_MODIFY]]);

        $employeeId = self::getParameter('employee-id', dataType: 'int', isCompulsory: true);
        $name = self::getParameter('employee-name');
        $address = self::getParameter('address');
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $joinDate = self::getParameter('join-date');
        $leftDate = self::getParameter('left-date');

        if (Employees::updateEmployee($employeeId, $name, $address, $email, $phoneNumber, $branchId, $joinDate, $leftDate))
            self::sendSuccess('Employee details were updated successfully.');
        else
            self::sendError('Failed to update employee data.');
    }

    public function deleteEmployee(): void
    {
        self::checkPermissions(['employees' => [User::PERMISSION_DELETE]]);

        $employeeId = self::getParameter('employee-id', dataType: 'int', isCompulsory: true);
        if (Employees::deleteEmployee($employeeId))
            self::sendSuccess('Successfully deleted the employee.');
        else
            self::sendError('Failed to delete the employee');
    }
}