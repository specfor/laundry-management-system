<?php

namespace LogicLeap\StockManagement\controllers\v1\stock_management;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\stock_management\Branches;
use LogicLeap\StockManagement\models\user_management\User;

class BranchController extends Controller
{   
    public function addBranch(): void
    {
        self::checkPermissions(['branches' => [User::PERMISSION_WRITE]]);

        $branchName = self::getParameter('branch-name', isCompulsory: true);
        $address = self::getParameter('address');
        $phoneNum = self::getParameter('phone-number');
        $managerId = self::getParameter('manager-id', dataType: 'int');

        $status = Branches::addNewBranch($branchName, $address, $managerId, $phoneNum);
        if (is_array($status))
            self::sendSuccess(['message' => 'New branch was created successfully.', 'branch-id' => $status['branch_id']]);
        elseif(is_string($status))
            self::sendError($status);
        else
            self::sendError('Failed to add new branch');
    }

    public function getBranches(): void
    {
        self::checkPermissions(['branches' => [User::PERMISSION_READ]]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $branchName = self::getParameter('branch-name');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $address = self::getParameter('address');
        $phoneNum = self::getParameter('phone-number');
        $managerId = self::getParameter('manager-id', dataType: 'int');

        $data = Branches::getBranches($pageNum, $branchId, $branchName, $address, $managerId, $phoneNum);
        self::sendSuccess(['branches' => $data]);
    }

    public function updateBranch(): void
    {
        self::checkPermissions(['branches' => [User::PERMISSION_MODIFY]]);

        $branchId = self::getParameter('branch-id', dataType: 'int', isCompulsory: true);
        $branchName = self::getParameter('branch-name');
        $address = self::getParameter('address');
        $phoneNum = self::getParameter('phone-number');
        $managerId = self::getParameter('manager-id', dataType: 'int');
        if (Branches::updateBranch($branchId, $branchName, $address, $managerId, $phoneNum))
            self::sendSuccess('New branch was created successfully.');
        else
            self::sendError('Failed to update branch details.');
    }

    public function deleteBranch(): void
    {
        self::checkPermissions(['branches' => [User::PERMISSION_DELETE]]);

        $branchId = self::getParameter('branch-id', dataType: 'int', isCompulsory: true);
        if (Branches::deleteBranch($branchId))
            self::sendSuccess('Successfully deleted the branch.');
        else
            self::sendError('Failed to delete the branch');
    }
}