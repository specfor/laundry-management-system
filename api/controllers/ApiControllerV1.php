<?php

namespace LogicLeap\StockManagement\controllers;

use Exception;
use LogicLeap\StockManagement\core\Application;
use LogicLeap\StockManagement\core\Request;
use LogicLeap\StockManagement\core\SecureToken;
use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\Authorization;
use LogicLeap\StockManagement\models\Branches;
use LogicLeap\StockManagement\models\Customers;
use LogicLeap\StockManagement\models\User;

class ApiControllerV1 extends API
{
    public function addCustomer(): void
    {
        self::checkLoggedIn();
        $params = Application::$app->request->getBodyParams();
        $email = $params['email'] ?? "";
        $customerName = $params['customer-name'] ?? "";
        $phoneNumber = $params['phone-number'] ?? "";
        $address = $params['address'] ?? "";

        $userId = self::getUserId();
        $branchId = User::getUserBranchId($userId);
        if (Customers::addNewCustomer($customerName, $email, $phoneNumber, $address, $branchId)) {
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

    public function addBranch(): void{
        self::checkLoggedIn(true);
        $params = Application::$app->request->getBodyParams();

        $branchName = $params['branch-name'] ?? null;
        $address = $params['address'] ?? null;
        $managerId = $params['manager-id'] ?? null;
        if (Branches::addNewBranch($branchName, $address, $managerId))
            self::sendSuccess(['message' => 'New branch was created successfully.']);
    }

    public function getBranches():void
    {
        self::checkLoggedIn(true);
        $params = Application::$app->request->getBodyParams();

        $startIndex = self::getConvertedTo('start', $params['start'] ?? '0', 'int');
        $data = Branches::getBranches($startIndex);
        self::sendSuccess(['branches' => $data]);
    }

    public function updateBranch():void{
        self::checkLoggedIn(true);
        $params = Application::$app->request->getBodyParams();

        $branchName = $params['branch-name'] ?? null;
        $address = $params['address'] ?? null;
        $managerId = $params['manager-id'] ?? null;
        if (Branches::updateBranch($branchName, $address, $managerId))
            self::sendSuccess(['message' => 'New branch was created successfully.']);
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
                $token = SecureToken::generateToken();
                Authorization::markSuccessfulLogin($user->userId, $token, Request::getRequestIp());
                $returnPayload = [
                    'message' => 'Login successful.',
                    'token' => $token
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
    private static function checkLoggedIn(bool $requireAdmin = false): void
    {
        preg_match('/Bearer\s(\S+)/', self::getAuthorizationHeader(), $matches);

        if (!$matches || !Authorization::isValidToken($matches[1])) {
            self::sendResponse(self::STATUS_CODE_UNAUTHORIZED, self::STATUS_MSG_UNAUTHORIZED,
                ['message' => 'You are not authorized to perform this action.']);
            exit();
        }
        if ($requireAdmin){
            if (!User::isAdmin(self::getUserId())){
                self::sendResponse(self::STATUS_CODE_UNAUTHORIZED, self::STATUS_MSG_UNAUTHORIZED,
                    ['message' => 'You are not authorized to perform this action.']);
                exit();
            }
        }
    }

    private static function getUserId(): int
    {
        preg_match('/Bearer\s(\S+)/', self::getAuthorizationHeader(), $matches);
        return Authorization::getUserId($matches[1]);
    }

    private static function getAuthorizationHeader():string|null{
        $authHeader = null;
        if (isset($_SERVER['Authorization'])) {
            $authHeader = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $authHeader = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $authHeader = trim($requestHeaders['Authorization']);
            }
        }
        return $authHeader;
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
}