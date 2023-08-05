<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\user_management\User;

class UserController extends API
{   
    public function addUser(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_WRITE]]);

        $username = self::getParameter('username', isCompulsory: true);
        $email = self::getParameter('email');
        $firstname = self::getParameter('firstname');
        $lastname = self::getParameter('lastname');
        $password = self::getParameter('password', isCompulsory: true);
        $role = self::getParameter('role', isCompulsory: true);
        $branchId = self::getParameter('branch-id', dataType: 'int');

        $status = User::createNewUser($username, $password, $role, $email, $firstname, $lastname, $branchId);
        if (is_array($status))
            self::sendSuccess($status);
        else
            self::sendError($status);
    }

    public function getUsers(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_READ]]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $username = self::getParameter('username');
        $name = self::getParameter('name');
        $email = self::getParameter('email');
        $role = self::getParameter('role');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        $data = User::getUsers($pageNum, $username, $name, $email, $role, $branchId);
        self::sendSuccess(['users' => $data]);
    }

    public function deleteUser(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_DELETE]]);

        $deleteUserId = self::getParameter('user-id', dataType: 'int', isCompulsory: true);

        $deleteUserRole = User::getUserRole($deleteUserId);

        if ($deleteUserRole == User::ROLE_SUPER_ADMINISTRATOR)
            self::sendError('Failed to delete the user.');

        if ($deleteUserId == self::getUserId()) {
            self::sendError('You cannot delete your account.');
        }

        if (User::deleteUser($deleteUserId))
            self::sendSuccess('Successfully deleted the user.');
        else
            self::sendError('Failed to delete the user.');
    }

    public function updateUser(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_MODIFY]]);

        $userId = self::getParameter('user-id', dataType: 'int', isCompulsory: true);
        $email = self::getParameter('email');
        $firstname = self::getParameter('firstname');
        $lastname = self::getParameter('lastname');
        $password = self::getParameter('password');
        $role = self::getParameter('role');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        $status = User::updateUser($userId, $password, $role, $email, $firstname, $lastname, $branchId);
        if ($status === true)
            self::sendSuccess('Successfully updated the user.');
        elseif ($status === false)
            self::sendError('Failed to update the user.');
        else
            self::sendError($status);
    }
}