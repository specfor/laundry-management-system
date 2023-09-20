<?php

namespace LogicLeap\StockManagement\controllers\v1\user_management;

use LogicLeap\PhpServerCore\FileHandler;
use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\user_management\User;
use LogicLeap\StockManagement\util\Util;

class UserController extends Controller
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
        $userId = self::getParameter('user-id', dataType: 'int');

        $status = User::createNewUser($username, $password, $role, $email, $firstname, $lastname, $branchId, $userId);
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
        self::sendSuccess($data);
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

    public function getUserLoginHistory(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_READ]]);

        $userId = self::getParameter('user-id', dataType: 'int', isCompulsory: true);
        $date = self::getParameter('login-date');
        $ipAddress = self::getParameter('ip-address');
        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');

        $data = User::getUserLoginHistory($userId, $date, $ipAddress, $pageNumber);
        self::sendSuccess($data);
    }

    public function sendPasswordResetLink(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $usernameOrEmail = self::getParameter('username-or-email', isCompulsory: true);

        $status = User::sendPassResetEmail($usernameOrEmail);
        if ($status === true)
            self::sendSuccess('If there is any user with the given username/email, password reset link ' .
                'will be sent to the email linked to the account.');
        else
            self::sendError($status);
    }

    public function checkPassResetTokenValidity(): void
    {
        $token = self::getParameter('token', isCompulsory: true);

        $status = User::isValidPassResetToken($token);
        if ($status === true)
            self::sendSuccess('This token is valid.');
        else
            self::sendError($status);
    }

    public function updateCurrentPassword(): void
    {
        self::checkPermissions();

        $currentPassword = self::getParameter('current-password', isCompulsory: true);
        $newPassword = self::getParameter('new-password', isCompulsory: true);

        $status = User::updateUserPassword(self::getUserId(), $currentPassword, $newPassword);
        if ($status === true)
            self::sendSuccess("Successfully updated the account password.");
        self::sendError($status);
    }

    public function resetPassword(): void
    {
        $token = self::getParameter('token', isCompulsory: true);
        $newPassword = self::getParameter('password', isCompulsory: true);

        $status = User::resetUserPassword($token, $newPassword);
        if ($status === true) {
            self::sendSuccess('Successfully updated the user password.');
        } else
            self::sendError($status);
    }

    public function getProfile(): void
    {
        self::checkPermissions();

        $userId = self::getUserId();

        $loginHistory = User::getUserLoginHistory($userId)['history'];
        $userInfo = User::getUserProfile($userId);
        $userInfo['login-history'] = $loginHistory;
        self::sendSuccess($userInfo);
    }

    public function getProfilePicture(array $args): void
    {
        self::checkPermissions();

        if (!FileHandler::streamFile("/profile_pictures/" . $args[0]))
            self::sendError('Invalid file name');
    }

    public function uploadProfilePicture(): void
    {
        self::checkPermissions();

        $userId = self::getUserId();
        self::handleUploadUserProfilePicture($userId);
    }

    public function uploadUserProfilePicture(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_MODIFY]]);

        $userId = $_SERVER['HTTP_USER_ID'] ?? null;
        if ($userId === null)
            self::sendError("Missing 'user-id' header.");

        self::handleUploadUserProfilePicture(intval($userId));
    }

    private static function handleUploadUserProfilePicture(int $userId): void
    {
        $user = User::getUsers(userId: $userId)['users'];
        if (empty($user))
            self::sendError('Invalid user Id.');

        $status = User::uploadUserProfilePicture($userId);
        if (is_array($status))
            self::sendSuccess(['message' => 'Successfully updated the profile photo.', 'profile-photo' => $status['photo']]);
        elseif ($status === false)
            self::sendError('Failed to update the profile photo.');
        else
            self::sendError($status);
    }

    public function uploadFiles(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_MODIFY]]);

        $userId = $_SERVER['HTTP_USER_ID'] ?? null;
        if ($userId === null)
            self::sendError("Missing 'user-id' header.");

        $status = User::uploadUserFiles(intval($userId));
        if ($status === true)
            self::sendSuccess('Successfully uploaded the file.');
        else
            self::sendError($status);
    }

    public function getUploadFileList(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_READ]]);

        $userId = self::getParameter('user-id', dataType: 'int');
        $response = User::getUploadedFileList($userId);
        if (is_string($response))
            self::sendError($response);
        else
            self::sendSuccess($response);
    }

    public function getFile(array $args): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_READ]]);

        $documentID = Util::getConvertedTo($args[0], 'int');
        if ($documentID === null)
            self::sendError('Invalid document id.');
        $status = User::streamFile($documentID);
        if ($status !== null)
            self::sendError($status);
    }

    public function deleteFile(): void
    {
        self::checkPermissions(['users' => [User::PERMISSION_MODIFY]]);

        $documentID = self::getParameter('document-id', dataType: 'int', isCompulsory: true);
        $status = User::deleteDocument($documentID);
        if ($status===true)
            self::sendSuccess('Successfully deleted the document.');
        else
            self::sendError($status);
    }
}