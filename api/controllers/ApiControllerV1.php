<?php

namespace LogicLeap\StockManagement\controllers;

use Exception;
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
    private static function sendError(string $message): void
    {
        self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_ERROR,
            ['error' => $message]);
        exit();
    }

    private static function sendSuccess(array $body): void
    {
        self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_SUCCESS, $body);
    }

    public function addCustomer(): void
    {
        self::checkLoggedIn();
        $params = Application::$app->request->getBodyParams();
        $email = $params['email'] ?? "";
        $firstname = $params['firstname'] ?? "";
        $lastname = $params['lastname'] ?? "";
        $phoneNumber = $params['phone-number'] ?? "";
        $address = $params['address'] ?? "";

        $userId = self::getUserId();
        $branchId = User::getUserBranchId($userId);
        if (Customers::addNewCustomer($firstname, $lastname, $email, $phoneNumber, $address, $branchId)) {
            self::sendSuccess(['message' => 'New customer was added successfully.']);
        }
    }

    public function getCustomers(): void
    {
        self::checkLoggedIn();
        $params = Application::$app->request->getBodyParams();

        $startIndex = self::getConvertedTo('start', $params['start'] ?? '0', 'int');
        $branchId = User::getUserBranchId(self::getUserId());
        $data = Customers::getCustomers($branchId, $startIndex);
        self::sendSuccess(['customers' => $data]);
    }

    public function login(): void
    {
        if (Application::$app->request->isPost()) {
            $params = Application::$app->request->getBodyParams();
            if (!isset($params['username']) || !isset($params['password'])) {
                self::sendError('Not all required fields were supplied.');
            }
            $user = new User();
            $username = $params['username'] ?? "";
            $password = $params['password'] ?? "";
            if (!$username || !$password)
                self::sendError("Not all required fields are provided.");
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
                self::sendError('Incorrect Username or Password.');
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
     * Check whether requests are coming from authorized users. If not send "401" unauthorized error message.
     */
    private static function checkLoggedIn(): void
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])){
            self::sendResponse(self::STATUS_CODE_UNAUTHORIZED, self::STATUS_MSG_UNAUTHORIZED,
                ['message' => 'You are not authorized to perform this action.']);
            exit();
        }
        !preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches);

        if (!$matches[1] || !JWT::isValidToken($matches[1]) || JWT::isExpired($matches[1])) {
            self::sendResponse(self::STATUS_CODE_UNAUTHORIZED, self::STATUS_MSG_UNAUTHORIZED,
                ['message' => 'You are not authorized to perform this action.']);
            exit();
        }
    }

    private static function getUserId(): int
    {
        preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches);
        return JWT::getTokenPayload($matches[1])['id'];
    }

    /**
     * Convert to a specific data type. If value cannot be converted, send an error to user.
     * @param string $parameterName request parameter to send with error message
     * @param mixed $value value to be converted
     * @param string $dataType 'int', 'float', 'bool'. Should be one of those
     * @return mixed Converted data
     */
    private static function getConvertedTo(string $parameterName, mixed $value, string $dataType): mixed
    {
        try {
            if ($dataType == 'int')
                $value = intval($value);
            elseif ($dataType == 'float')
                $value = floatval($value);
            elseif ($dataType == 'bool')
                $value = boolval($value);

            return $value;
        } catch (Exception) {
            self::sendError("$parameterName must be type '$dataType'");
            return null;
        }
    }
}