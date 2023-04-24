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
use LogicLeap\StockManagement\models\Employees;
use LogicLeap\StockManagement\models\User;

class ApiControllerV1 extends API
{
    public function addCustomer(): void
    {
        self::checkPermissions();

        $customerName = self::getParameter('customer-name', isCompulsory: true);
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $address = self::getParameter('address');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        if (Customers::addNewCustomer($customerName, $email, $phoneNumber, $address, $branchId))
            self::sendSuccess(['message' => 'New customer was added successfully.']);
        else
            self::sendError('Failed to add new customer');
    }

    public function getCustomers(): void
    {
        self::checkPermissions();

        $startIndex = self::getParameter('start', 0, 'int');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        $data = Customers::getCustomers($branchId, $startIndex);
        self::sendSuccess(['customers' => $data]);
    }

    public function updateCustomer(): void
    {
        self::checkPermissions();

        $customerId = self::getParameter('customer-id', isCompulsory: true);
        $email = self::getParameter('email');
        $customerName = self::getParameter('customer-name');
        $phoneNumber = self::getParameter('phone-number');
        $address = self::getParameter('address');
        $banned = self::getParameter('banned', dataType: 'bool');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        if (Customers::updateCustomer($customerId, $customerName, $email, $phoneNumber, $address, $branchId, $banned))
            self::sendSuccess(['message' => 'Branch details were updated successfully.']);
        else
            self::sendError('Failed to update customer details.');
    }

    public function deleteCustomer(): void
    {
        self::checkPermissions();

        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        if (Customers::deleteCustomer($customerId))
            self::sendSuccess(['message' => 'Successfully deleted the customer.']);
        else
            self::sendError('Failed to delete the customer');
    }

    public function addBranch(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $branchName = self::getParameter('branch-name');
        $address = self::getParameter('address');
        $managerId = self::getParameter('manager-id', dataType: 'int');

        if (Branches::addNewBranch($branchName, $address, $managerId))
            self::sendSuccess(['message' => 'New branch was created successfully.']);
        else
            self::sendError('Failed to add new branch');
    }

    public function getBranches(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $startIndex = self::getParameter('start', 0, 'int');
        $data = Branches::getBranches($startIndex);
        self::sendSuccess(['branches' => $data]);
    }

    public function updateBranch(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $branchId = self::getParameter('branch-id', dataType: 'int', isCompulsory: true);
        $branchName = self::getParameter('branch-name');
        $address = self::getParameter('address');
        $managerId = self::getParameter('manager-id', dataType: 'int');
        if (Branches::updateBranch($branchId, $branchName, $address, $managerId))
            self::sendSuccess(['message' => 'New branch was created successfully.']);
        else
            self::sendError('Failed to update branch details.');
    }

    public function deleteBranch(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $branchId = self::getParameter('branch-id', dataType: 'int', isCompulsory: true);
        if (Branches::deleteBranch($branchId))
            self::sendSuccess(['message' => 'Successfully deleted the branch.']);
        else
            self::sendError('Failed to delete the branch');
    }

    public function addEmployee(): void
    {
        self::checkPermissions(User::ROLE_MANAGER);

        $name = self::getParameter('employee-name', isCompulsory: true);
        $address = self::getParameter('address');
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $branchId = self::getParameter('branch-id');
        $joinDate = self::getParameter('join-date');
        $leftDate = self::getParameter('left-date');

        if (Employees::addEmployee($name, $address, $email, $phoneNumber, $branchId, $joinDate, $leftDate))
            self::sendSuccess(['message' => 'New branch was created successfully.']);
        else
            self::sendError('Failed to add new employee.');
    }

    public function getEmployees(): void
    {
        self::checkPermissions(User::ROLE_MANAGER);

        $startIndex = self::getParameter('start', 0, 'int');
        $data = Employees::getEmployees($startIndex);
        self::sendSuccess(['employees' => $data]);
    }

    public function updateEmployee(): void
    {
        self::checkPermissions(User::ROLE_MANAGER);

        $employeeId = self::getParameter('employee-id', dataType: 'int', isCompulsory: true);
        $name = self::getParameter('employee-name');
        $address = self::getParameter('address');
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $branchId = self::getParameter('branch-id');
        $joinDate = self::getParameter('join-date');
        $leftDate = self::getParameter('left-date');

        if (Employees::updateEmployee($employeeId, $name, $address, $email, $phoneNumber, $branchId, $joinDate, $leftDate))
            self::sendSuccess(['message' => 'New branch was created successfully.']);
        else
            self::sendError('Failed to add new employee.');
    }

    public function deleteEmployee(): void
    {
        self::checkPermissions(User::ROLE_MANAGER);

        $employeeId = self::getParameter('employee-id', dataType: 'int', isCompulsory: true);
        if (Employees::deleteEmployee($employeeId))
            self::sendSuccess(['message' => 'Successfully deleted the employee.']);
        else
            self::sendError('Failed to delete the employee');
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

    public function addUser(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $username = self::getParameter('username', isCompulsory: true);
        $email = self::getParameter('email');
        $firstname = self::getParameter('firstname');
        $lastname = self::getParameter('lastname');
        $password = self::getParameter('password', isCompulsory: true);
        $role = self::getParameter('role', isCompulsory: true);
        $branchId = self::getParameter('branch-id');

        $status = User::createNewUser($username, $password, $role, $email, $firstname, $lastname, $branchId);
        if ($status === 'New user created successfully.')
            self::sendSuccess(['message' => $status]);
        else
            self::sendError($status);
    }

    public function getUsers(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $startIndex = self::getParameter('start', 0, 'int');
        $data = User::getUsers($startIndex);
        self::sendSuccess(['users' => $data]);
    }

    public function deleteUser(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $deleteUserId = self::getParameter('user-id', dataType: 'int', isCompulsory: true);

        $deleteUserRole = User::getUserRole($deleteUserId);

        if ($deleteUserRole == User::ROLE_SUPER_ADMINISTRATOR)
            self::sendError('Failed to delete the user.');

        if ($deleteUserRole == User::ROLE_ADMINISTRATOR) {
            if ($deleteUserId == self::getUserId()) {
                self::sendError('You cannot delete your administrator account.');
            }
        }

        if (User::deleteUser($deleteUserId))
            self::sendSuccess(['message' => 'Successfully deleted the user.']);
        else
            self::sendError('Failed to delete the user.');
    }

    public function updateUser(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $userId = self::getParameter('user-id', dataType: 'int', isCompulsory: true);
        $email = self::getParameter('email');
        $firstname = self::getParameter('firstname');
        $lastname = self::getParameter('lastname');
        $password = self::getParameter('password');
        $role = self::getParameter('role');
        $branchId = self::getParameter('branch-id');

        if (User::updateUser($userId,$password,$role,$email,$firstname,$lastname,$branchId))
            self::sendSuccess(['message'=>'Successfully updated the user.']);
        else
            self::sendError('Failed to update the user.');
    }

    /**
     * Check whether requests are coming from authorized users. If not send "401" unauthorized error message.
     * @param int $requiredMinimumUserRole Minimum user role required to perform the action.
     */
    private static function checkPermissions(int $requiredMinimumUserRole = User::ROLE_CASHIER): void
    {
        preg_match('/Bearer\s(\S+)/', self::getAuthorizationHeader(), $matches);

        if (!$matches || !Authorization::isValidToken($matches[1])) {
            self::sendResponse(self::STATUS_CODE_UNAUTHORIZED, self::STATUS_MSG_UNAUTHORIZED,
                ['message' => 'You are not authorized to perform this action.']);
            exit();
        }

        if (User::getUserRole(self::getUserId()) < $requiredMinimumUserRole) {
            self::sendResponse(self::STATUS_CODE_UNAUTHORIZED, self::STATUS_MSG_UNAUTHORIZED,
                ['message' => 'You are not authorized to perform this action.']);
            exit();
        }

    }

    private static function getUserId(): int
    {
        preg_match('/Bearer\s(\S+)/', self::getAuthorizationHeader(), $matches);
        return Authorization::getUserId($matches[1]);
    }

    private static function getAuthorizationHeader(): string|null
    {
        $authHeader = null;
        if (isset($_SERVER['Authorization'])) {
            $authHeader = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
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
     * Retrieve parameters passed either by GET or POST methods.
     * @param string $parameterIdentifier Parameter name
     * @param mixed $defaultValue Default value to set if parameter is not passed.
     * @param string $dataType Datatype if needed to get converted to a specific data type. Send an error message to
     *                          the user if passed data cannot be converted to the specified type.
     * @param bool $isCompulsory If set to True, send an error message if specified parameter is not passed.
     * @return mixed Value of the parameter.
     */
    private static function getParameter(string $parameterIdentifier, mixed $defaultValue = null,
                                         string $dataType = 'string', bool $isCompulsory = false): mixed
    {
        $params = Application::$app->request->getBodyParams();

        if (!isset($params[$parameterIdentifier]))
            if ($isCompulsory)
                self::sendError("Required parameter '$parameterIdentifier' is missing.");
            else
                return $defaultValue;
        if ($dataType == 'string')
            return $params[$parameterIdentifier];
        else
            return self::getConvertedTo($parameterIdentifier, $params[$parameterIdentifier], $dataType);
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