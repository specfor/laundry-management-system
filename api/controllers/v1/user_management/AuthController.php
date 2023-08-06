<?php

namespace LogicLeap\StockManagement\controllers\v1\user_management;

use LogicLeap\PhpServerCore\Request;
use LogicLeap\PhpServerCore\SecureToken;
use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\user_management\Authorization;
use LogicLeap\StockManagement\models\user_management\User;

class AuthController extends API
{   
    public function whoAmI(): void
    {
        self::checkPermissions();

        $userId = self::getUserId();
        self::sendSuccess(['user-id' => $userId]);
    }

    public function login(): void
    {
        $username = self::getParameter('username', isCompulsory: true);
        $password = self::getParameter('password', isCompulsory: true);
        $user = new User();

        if ($user->validateUser($username, $password)) {
            $token = SecureToken::generateToken();
            Authorization::markSuccessfulLogin($user->userId, $token, Request::getRequestIp());
            $returnPayload = [
                'message' => 'Login successful.',
                'token' => $token
            ];
            self::sendSuccess($returnPayload);
        } else {
            self::sendError('Incorrect Username or Password.');
        }
    }
}