<?php

namespace LogicLeap\StockManagement\controllers;

use Exception;
use LogicLeap\StockManagement\core\Application;
use LogicLeap\StockManagement\core\MigrationManager;
use LogicLeap\StockManagement\core\Request;
use LogicLeap\StockManagement\core\SecureToken;
use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\Authorization;
use LogicLeap\StockManagement\models\Branches;
use LogicLeap\StockManagement\models\Customers;
use LogicLeap\StockManagement\models\Employees;
use LogicLeap\StockManagement\models\Items;
use LogicLeap\StockManagement\models\Orders;
use LogicLeap\StockManagement\models\PriceCategories;
use LogicLeap\StockManagement\models\User;

class ApiControllerV1 extends API
{
    public function __construct()
    {
        $maintenanceModeFilePath = Application::$ROOT_DIR . "/maintenanceLock.lock";
        $migrationModeFilePath = Application::$ROOT_DIR . "/migrationLock.lock";
        if (is_file($migrationModeFilePath)) {
            $migrationManager = new MigrationManager();
            $migrationManager->startMigration();
        }

        // If in maintenance mode, maintenance page is displayed. Application exits.
        if (is_file($maintenanceModeFilePath)) {
            if (!self::isSiteMigrator()) {
                self::sendResponse(API::STATUS_CODE_MAINTENANCE, API::STATUS_MSG_MAINTENANCE,
                    ['error' => 'Server is under maintenance']);
                exit();
            }
        }

        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
    }

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
            self::sendSuccess('New customer was added successfully.');
        else
            self::sendError('Failed to add new customer');
    }

    public function getCustomers(): void
    {
        self::checkPermissions();

        $pageNum = self::getParameter('page-num', 0, 'int');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $email = self::getParameter('email');
        $phoneNum = self::getParameter('phone-number');
        $name = self::getParameter('name');
        $address = self::getParameter('address');
        $banned = self::getParameter('banned', dataType: 'bool');
        $joinDate = self::getParameter('join-date');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        $data = Customers::getCustomers($branchId, $email, $phoneNum, $name, $address, $banned, $joinDate, $pageNum);
        self::sendSuccess(['customers' => $data]);
    }

    public function updateCustomer(): void
    {
        self::checkPermissions();

        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        $email = self::getParameter('email');
        $customerName = self::getParameter('customer-name');
        $phoneNumber = self::getParameter('phone-number');
        $address = self::getParameter('address');
        $banned = self::getParameter('banned', dataType: 'bool');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        if (Customers::updateCustomer($customerId, $customerName, $email, $phoneNumber, $address, $branchId, $banned))
            self::sendSuccess('Customer details were updated successfully.');
        else
            self::sendError('Failed to update customer details.');
    }

    public function deleteCustomer(): void
    {
        self::checkPermissions();

        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        if (Customers::deleteCustomer($customerId))
            self::sendSuccess('Successfully deleted the customer.');
        else
            self::sendError('Failed to delete the customer');
    }

    public function addBranch(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $branchName = self::getParameter('branch-name', isCompulsory: true);
        $address = self::getParameter('address');
        $phoneNum = self::getParameter('phone-number');
        $managerId = self::getParameter('manager-id', dataType: 'int');

        if (Branches::addNewBranch($branchName, $address, $managerId, $phoneNum))
            self::sendSuccess('New branch was created successfully.');
        else
            self::sendError('Failed to add new branch');
    }

    public function getBranches(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $branchName = self::getParameter('branch-name');
        $address = self::getParameter('address');
        $phoneNum = self::getParameter('phone-number');
        $managerId = self::getParameter('manager-id', dataType: 'int');

        $data = Branches::getBranches($pageNum, $branchName, $address, $managerId, $phoneNum);
        self::sendSuccess(['branches' => $data]);
    }

    public function updateBranch(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

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
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $branchId = self::getParameter('branch-id', dataType: 'int', isCompulsory: true);
        if (Branches::deleteBranch($branchId))
            self::sendSuccess('Successfully deleted the branch.');
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
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $joinDate = self::getParameter('join-date');
        $leftDate = self::getParameter('left-date');

        if (Employees::addEmployee($name, $address, $email, $phoneNumber, $branchId, $joinDate, $leftDate))
            self::sendSuccess('New branch was created successfully.');
        else
            self::sendError('Failed to add new employee.');
    }

    public function getEmployees(): void
    {
        self::checkPermissions(User::ROLE_MANAGER);

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
        self::checkPermissions(User::ROLE_MANAGER);

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
        self::checkPermissions(User::ROLE_MANAGER);

        $employeeId = self::getParameter('employee-id', dataType: 'int', isCompulsory: true);
        if (Employees::deleteEmployee($employeeId))
            self::sendSuccess('Successfully deleted the employee.');
        else
            self::sendError('Failed to delete the employee');
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

    public function addUser(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $username = self::getParameter('username', isCompulsory: true);
        $email = self::getParameter('email');
        $firstname = self::getParameter('firstname');
        $lastname = self::getParameter('lastname');
        $password = self::getParameter('password', isCompulsory: true);
        $role = self::getParameter('role', isCompulsory: true);
        $branchId = self::getParameter('branch-id', dataType: 'int');

        $status = User::createNewUser($username, $password, $role, $email, $firstname, $lastname, $branchId);
        if ($status === 'New user created successfully.')
            self::sendSuccess($status);
        else
            self::sendError($status);
    }

    public function getUsers(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

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
            self::sendSuccess('Successfully deleted the user.');
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

        $status = User::updateUser($userId, $password, $role, $email, $firstname, $lastname, $branchId);
        if ($status === true)
            self::sendSuccess('Successfully updated the user.');
        elseif ($status === false)
            self::sendError('Failed to update the user.');
        else
            self::sendError($status);
    }

    public function getPriceCategories(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $categoryName = self::getParameter('category-name');

        $data = PriceCategories::getCategories($pageNum, $categoryName);
        self::sendSuccess(['categories' => $data]);
    }

    public function addPriceCategory(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $categoryName = self::getParameter('category-name', isCompulsory: true);

        $status = PriceCategories::addCategory($categoryName);
        if ($status === true)
            self::sendSuccess('New category was created successfully.');
        elseif ($status === false)
            self::sendError('Failed to add new category.');
        else
            self::sendError($status);
    }

    public function updatePriceCategory(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $categoryId = self::getParameter('category-id', dataType: 'int', isCompulsory: true);
        $categoryName = self::getParameter('category-name', isCompulsory: true);

        if (PriceCategories::updateCategory($categoryId, $categoryName))
            self::sendSuccess('Category was updated successfully.');
        else
            self::sendError('Failed to update the category.');
    }

    public function deletePriceCategory(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $categoryId = self::getParameter('category-id', dataType: 'int', isCompulsory: true);

        if (PriceCategories::deleteCategory($categoryId))
            self::sendSuccess('Category was deleted successfully.');
        else
            self::sendError('Failed to delete the category.');
    }

    public function getItems(): void
    {
        self::checkPermissions();

        $pageNum = self::getParameter('page-num', 0, 'int');
        $itemName = self::getParameter('item-name');
        $itemId = self::getParameter('item-id', dataType: 'int');
        $price = self::getParameter('item-price', dataType: 'float');
        $blocked = self::getParameter('blocked', dataType: 'bool');

        $data = Items::getItems($pageNum, $itemId, $itemName, $price, $blocked);
        self::sendSuccess(['items' => $data]);
    }

    public function addItem(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $itemName = self::getParameter('item-name', isCompulsory: true);
        $prices = self::getParameter('item-price', defaultValue: [], dataType: 'array');
        $blocked = self::getParameter('blocked', defaultValue: false, dataType: 'bool');

        $status = Items::addItem($itemName, $prices, $blocked);
        if ($status === true)
            self::sendSuccess('New item was added successfully.');
        elseif ($status === false)
            self::sendError('Failed to add new item.');
        else
            self::sendError($status);
    }

    public function updateItem(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $itemId = self::getParameter('item-id', dataType: 'int', isCompulsory: true);
        $itemName = self::getParameter('item-name');
        $prices = self::getParameter('item-price', dataType: 'array');
        $blocked = self::getParameter('blocked', dataType: 'bool');

        $status = Items::updateItem($itemId, $itemName, $prices, $blocked);
        if ($status === true)
            self::sendSuccess('Item was updated successfully.');
        elseif ($status === false)
            self::sendError('Failed to update the item.');
        else
            self::sendError($status);
    }

    public function deleteItem(): void
    {
        self::checkPermissions(User::ROLE_ADMINISTRATOR);

        $itemId = self::getParameter('item-id', dataType: 'int', isCompulsory: true);

        if (Items::deleteItem($itemId))
            self::sendSuccess('Item was deleted successfully.');
        else
            self::sendError('Failed to delete the item.');
    }

    public function getOrders()
    {
        self::checkPermissions();

        $pageNumber = self::getParameter('page-num',defaultValue: 0, dataType: 'int');
        $orderId = self::getParameter('order-id', dataType: 'int');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $addedDate = self::getParameter('added-date');
        $orderStatus = self::getParameter('order-status');

        $orders = Orders::getOrders($pageNumber, $orderId, $addedDate, $branchId, $orderStatus);
        self::sendSuccess(['orders' => $orders]);
    }

    public function addOrder()
    {
        self::checkPermissions();

        $items = self::getParameter('items', dataType: 'array', isCompulsory: true);
        $totalPrice = self::getParameter('total-price', dataType: 'float');

        $branchId = User::getUserBranchId(self::getUserId());
        if ($branchId == 0)
            $branchId = self::getParameter('branch-id', dataType: 'int');

        $status = Orders::addNewOrder($items, $totalPrice, $branchId);
        if ($status === true)
            self::sendSuccess('New order added successfully.');
        elseif ($status === false)
            self::sendError("Failed to add new order.");
        else
            self::sendError($status);
    }

    public function updateOrder()
    {

    }

    public function deleteOrder()
    {
        self::checkPermissions(User::ROLE_MANAGER);

        $orderId = self::getParameter('order-id', dataType: 'int', isCompulsory: true);

        if (Orders::deleteOrder($orderId))
            self::sendSuccess("Order deleted successfully.");
        else
            self::sendError('Failed to delete the order.');
    }

    /**
     * Check whether requests are coming from authorized users. If not send "401" unauthorized error message.
     * @param int $requiredMinimumUserRole Minimum user role required to perform the action.
     */
    private static function checkPermissions(int $requiredMinimumUserRole = User::ROLE_CASHIER): void
    {
        preg_match('/Bearer\s(\S+)/', self::getAuthorizationHeader(), $matches);

        if (!$matches || !Authorization::isValidToken($matches[1])) {
            self::sendResponse(self::STATUS_CODE_FORBIDDEN, self::STATUS_MSG_FORBIDDEN,
                ['message' => 'You are not authorized to perform this action.']);
            exit();
        }

        if (User::getUserRole(self::getUserId()) > $requiredMinimumUserRole) {
            self::sendResponse(self::STATUS_CODE_FORBIDDEN, self::STATUS_MSG_FORBIDDEN,
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

    private static function isSiteMigrator(): bool
    {
        if (isset($_SERVER['X-Administrator-Token'])) {
            $migrationManager = new MigrationManager();
            if ($migrationManager->validateMigrationAuthToken($_SERVER['X-Administrator-Token']))
                return true;
        }
        return false;
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
            if ($dataType == 'string') {
                if (!is_string($value))
                    throw new Exception('string required.');
            } elseif ($dataType == 'int')
                $value = intval($value);
            elseif ($dataType == 'float')
                $value = floatval($value);
            elseif ($dataType == 'bool')
                $value = boolval($value);
            elseif ($dataType == 'array')
                if (!is_array($value))
                    throw new Exception('array required.');
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
            ['message' => $message]);
        exit();
    }

    private static function sendSuccess(array|string $body): void
    {
        if (is_array($body))
            self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_SUCCESS, $body);
        else
            self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_SUCCESS,
                ['message' => $body]);
        exit();
    }

    public function optionsRequest()
    {
        // Access-Control headers are received during OPTIONS requests
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    public function errorHandler(int|string $errorCode, string $errorMessage): void
    {
        if ($errorCode === 404)
            self::sendResponse(self::STATUS_CODE_NOTFOUND, self::STATUS_MSG_NOTFOUND,
                ['message' => $errorMessage]);
        elseif ($errorCode === 403)
            self::sendResponse(self::STATUS_CODE_FORBIDDEN, self::STATUS_MSG_FORBIDDEN,
                ['message' => $errorMessage]);
        else
            self::sendResponse(self::STATUS_CODE_SERVER_ERROR, self::STATUS_MSG_SERVER_ERROR,
                ['message' => 'A server error occurred.']);
        exit();
    }
}