<?php

namespace LogicLeap\StockManagement\controllers\v1\user_management;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\user_management\User;
use LogicLeap\StockManagement\models\user_management\UserRoles;

class UserRoleController extends Controller
{
    public function getAvailablePermissions(): void
    {
        self::checkPermissions();

        self::sendSuccess(['permissions' => User::getAllPermissions()]);
    }

    public function addUserRole(): void
    {
        self::checkPermissions(['user-roles' => [User::PERMISSION_WRITE]]);

        $name = self::getParameter('name', isCompulsory: true);
        $permissions = self::getParameter('permissions', dataType: 'array', isCompulsory: true);
        $description = self::getParameter('description');

        $status = UserRoles::createUserRole($name, $permissions, $description);
        if (is_array($status))
            self::sendSuccess($status);
        elseif ($status === false)
            self::sendError('Failed to create the user role.');
        else
            self::sendError($status);
    }

    public function getUserRoles(): void
    {
        self::checkPermissions(['user-roles' => [User::PERMISSION_READ]]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $roleId = self::getParameter('role-id', dataType: 'int');
        $name = self::getParameter('name');
        $description = self::getParameter('description');
        $permissions = self::getParameter('permissions', dataType: 'array');

        [$data, $count] = UserRoles::getUserRoles($pageNum, $roleId, $name, $permissions, $description);
        self::sendSuccess(['user-roles' => $data, 'record_count' => $count]);
    }

    public function updateUserRole(): void
    {
        self::checkPermissions(['user-roles' => [User::PERMISSION_MODIFY]]);

        $roleId = self::getParameter('role-id', dataType: 'int', isCompulsory: true);
        $name = self::getParameter('name');
        $permissions = self::getParameter('permissions', dataType: 'array');
        $description = self::getParameter('description');

        $status = UserRoles::updateUserRole($roleId, $name, $permissions, $description);
        if (is_string($status))
            self::sendError($status);
        elseif ($status)
            self::sendSuccess('Successfully updated the user role.');
        else
            self::sendError('Failed to update the user role.');
    }

    public function deleteUserRole(): void
    {
        self::checkPermissions(['user-roles' => [User::PERMISSION_DELETE]]);

        $roleId = self::getParameter('role-id', dataType: 'int', isCompulsory: true);

        $status = UserRoles::removeUserRole($roleId);

        if (is_string($status))
            self::sendError($status);
        elseif ($status)
            self::sendSuccess("Successfully deleted the user role.");
        else
            self::sendError('Failed to delete the user role.');
    }
}
