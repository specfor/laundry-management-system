<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\StockManagement\core\Application;
use LogicLeap\StockManagement\core\JWT;
use LogicLeap\StockManagement\core\Request;
use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\Customers;
use LogicLeap\StockManagement\models\User;

class ApiControllerV1 extends API
{
    // Interval is in seconds.
    private const JWT_TOKEN_EXPIRE_INTERVAL = 43200;

    /**
     * Send an error message to requested user and exit program execution.
     * @param string $message Error message to send.
     */
    private function sendError(string $message): void
    {
        self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_ERROR,
            ['error' => $message]);
        exit();
    }

    private function sendSuccess(array $body): void
    {
        self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_SUCCESS, $body);
    }

    public function addCustomer(): void
    {
        if (!self::isLoggedIn()) {
            $this->sendError('You are not authorized to perform the action.');
        }
        $params = Application::$app->request->getBodyParams();
        $email = $params['email'] ?? "";
        $firstname = $params['firstname'] ?? "";
        $lastname = $params['lastname'] ?? "";
        $phoneNumber = $params['phone-number'] ?? "";
        $address = $params['address'] ?? "";

        $userId = self::getUserId();
        $branchId = User::getUserBranchId($userId);
        if (Customers::addNewCustomer($firstname, $lastname, $email, $phoneNumber, $address, $branchId)) {
            $this->sendSuccess(['message' => 'New customer was added successfully.']);
        }
    }

    public function login(): void
    {
        if (Application::$app->request->isPost()) {
            $params = Application::$app->request->getBodyParams();
            if (!isset($params['username']) || !isset($params['password'])) {
                $this->sendError('Not all required fields were supplied.');
            }
            $user = new User();
            $username = $params['username'] ?? "";
            $password = $params['password'] ?? "";
            if (!$username || !$password)
                $this->sendError("Not");
            if ($user->validateUser($username, $password)) {
                $user->loadUserData($user->userId);
                $payload = [
                    'id' => $user->userId,
                    'email' => $user->email,
                    'role' => $user->getUserRoleText(),
                    'exp' => time() + self::JWT_TOKEN_EXPIRE_INTERVAL
                ];
                $jwt = JWT::generateToken($payload);
                $user->markSuccessfulLogin($user->userId, $jwt, Request::getRequestIp());
                $returnPayload = [
                    'message' => 'Login successful.',
                    'token' => $jwt
                ];
                self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_SUCCESS,
                    $returnPayload);
            } else {
                $this->sendError('Incorrect Username or Password.');
            }
        }
    }

    public function register(): void
    {
        $params = Application::$app->request->getBodyParams();
        $user = new User();
        $status = $user->createNewUser($params);
        if ($status === 'user created.') {
            self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_SUCCESS,
                ['message' => 'Registration successful.']);
        } else {
            self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_ERROR,
                ['error' => $status]);
        }
    }

    /**
     * Check whether requests are coming from authorized users.
     * @return bool Return true if requests contain proper Authorization header and token is not expired.
     */
    private static function isLoggedIn(): bool
    {
        if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            return false;
        }
        if (!$matches[1])
            return false;
        if (!JWT::isValidToken($matches[1]) || JWT::isExpired($matches[1]))
            return false;

        return true;
    }

    private static function getUserId():int{
        preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches);
        return JWT::getTokenPayload($matches[1])['id'];
    }
}