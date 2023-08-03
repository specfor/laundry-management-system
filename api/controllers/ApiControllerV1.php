<?php

namespace LogicLeap\StockManagement\controllers;

use Exception;
use LogicLeap\PhpServerCore\Application;
use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\PhpServerCore\Reports;
use LogicLeap\PhpServerCore\Request;
use LogicLeap\PhpServerCore\SecureToken;
use LogicLeap\PhpServerCore\ServerMetrics;
use LogicLeap\StockManagement\models\accounting\Accounting;
use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\user_management\Authorization;
use LogicLeap\StockManagement\models\stock_management\Branches;
use LogicLeap\StockManagement\models\stock_management\Customers;
use LogicLeap\StockManagement\models\stock_management\Employees;
use LogicLeap\StockManagement\models\stock_management\Items;
use LogicLeap\StockManagement\models\stock_management\Orders;
use LogicLeap\StockManagement\models\stock_management\Payments;
use LogicLeap\StockManagement\models\stock_management\PriceCategories;
use LogicLeap\StockManagement\models\user_management\User;
use LogicLeap\StockManagement\models\user_management\UserRoles;
use LogicLeap\StockManagement\models\accounting\Taxes;

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
        self::checkPermissions(['customers' => [User::PERMISSION_WRITE]]);

        $customerName = self::getParameter('customer-name', isCompulsory: true);
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $address = self::getParameter('address');
        $branchId = self::getParameter('branch-id', dataType: 'int');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        $status = Customers::addNewCustomer($customerName, $email, $phoneNumber, $address, $branchId);
        if (is_array($status))
            self::sendSuccess(['message' => 'New customer was added successfully.', 'customer-id' => $status['customer_id']]);
        else
            self::sendError('Failed to add new customer');
    }

    public function getCustomers(): void
    {
        self::checkPermissions(['customers' => User::PERMISSION_READ]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $customerId = self::getParameter('customer-id', dataType: 'int');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $email = self::getParameter('email');
        $phoneNum = self::getParameter('phone-number');
        $name = self::getParameter('name');
        $address = self::getParameter('address');
        $banned = self::getParameter('banned', dataType: 'bool');
        $joinDate = self::getParameter('join-date');

        if (!$branchId)
            $branchId = User::getUserBranchId(self::getUserId());

        $data = Customers::getCustomers($customerId, $branchId, $email, $phoneNum, $name, $address, $banned, $joinDate, $pageNum);
        self::sendSuccess(['customers' => $data]);
    }

    public function updateCustomer(): void
    {
        self::checkPermissions(['customers' => [User::PERMISSION_MODIFY]]);

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
        self::checkPermissions(['customers' => [User::PERMISSION_DELETE]]);

        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        if (Customers::deleteCustomer($customerId))
            self::sendSuccess('Successfully deleted the customer.');
        else
            self::sendError('Failed to delete the customer');
    }

    public function addBranch(): void
    {
        self::checkPermissions(['branches' => [User::PERMISSION_WRITE]]);

        $branchName = self::getParameter('branch-name', isCompulsory: true);
        $address = self::getParameter('address');
        $phoneNum = self::getParameter('phone-number');
        $managerId = self::getParameter('manager-id', dataType: 'int');

        $id = Branches::addNewBranch($branchName, $address, $managerId, $phoneNum);
        if (is_array($id))
            self::sendSuccess(['message' => 'New branch was created successfully.', 'branch-id' => $id['branch_id']]);
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

    public function addEmployee(): void
    {
        self::checkPermissions(['employees' => [User::PERMISSION_WRITE]]);

        $name = self::getParameter('employee-name', isCompulsory: true);
        $address = self::getParameter('address');
        $email = self::getParameter('email');
        $phoneNumber = self::getParameter('phone-number');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $joinDate = self::getParameter('join-date');
        $leftDate = self::getParameter('left-date');

        $status = Employees::addEmployee($name, $address, $email, $phoneNumber, $branchId, $joinDate, $leftDate);
        if (is_array($status))
            self::sendSuccess(['message' => 'New employee was added successfully.', 'employee_id' => $status['employee_id']]);
        else
            self::sendError('Failed to add new employee.');
    }

    public function getEmployees(): void
    {
        self::checkPermissions(['employees' => [User::PERMISSION_READ]]);

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
        self::checkPermissions(['employees' => [User::PERMISSION_MODIFY]]);

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
        self::checkPermissions(['employees' => [User::PERMISSION_DELETE]]);

        $employeeId = self::getParameter('employee-id', dataType: 'int', isCompulsory: true);
        if (Employees::deleteEmployee($employeeId))
            self::sendSuccess('Successfully deleted the employee.');
        else
            self::sendError('Failed to delete the employee');
    }

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

        $data = UserRoles::getUserRoles($pageNum, $roleId, $name, $permissions, $description);
        self::sendSuccess(['user-roles' => $data]);
    }

    public function updateUserRole()
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

    public function deleteUserRole()
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

    public function getPriceCategories(): void
    {
        self::checkPermissions(['categories' => [User::PERMISSION_READ]]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $categoryName = self::getParameter('category-name');

        $data = PriceCategories::getCategories($pageNum, $categoryName);
        self::sendSuccess(['categories' => $data]);
    }

    public function addPriceCategory(): void
    {
        self::checkPermissions(['categories' => [User::PERMISSION_WRITE]]);

        $categoryName = self::getParameter('category-name', isCompulsory: true);

        $status = PriceCategories::addCategory($categoryName);
        if (is_array($status))
            self::sendSuccess(['message' => 'New category was created successfully.', 'category-id' => $status['category_id']]);
        elseif ($status === false)
            self::sendError('Failed to add new category.');
        else
            self::sendError($status);
    }

    public function updatePriceCategory(): void
    {
        self::checkPermissions(['categories' => [User::PERMISSION_MODIFY]]);

        $categoryId = self::getParameter('category-id', dataType: 'int', isCompulsory: true);
        $categoryName = self::getParameter('category-name', isCompulsory: true);

        if (PriceCategories::updateCategory($categoryId, $categoryName))
            self::sendSuccess('Category was updated successfully.');
        else
            self::sendError('Failed to update the category.');
    }

    public function deletePriceCategory(): void
    {
        self::checkPermissions(['categories' => [User::PERMISSION_DELETE]]);

        $categoryId = self::getParameter('category-id', dataType: 'int', isCompulsory: true);

        if (PriceCategories::deleteCategory($categoryId))
            self::sendSuccess('Category was deleted successfully.');
        else
            self::sendError('Failed to delete the category.');
    }

    public function getItems(): void
    {
        self::checkPermissions(['products' => [User::PERMISSION_READ]]);

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
        self::checkPermissions(['products' => [User::PERMISSION_WRITE]]);

        $itemName = self::getParameter('item-name', isCompulsory: true);
        $prices = self::getParameter('item-price', defaultValue: [], dataType: 'array', isCompulsory: true);
        $blocked = self::getParameter('blocked', defaultValue: false, dataType: 'bool');

        $status = Items::addItem($itemName, $prices, $blocked);
        if (is_array($status))
            self::sendSuccess(['message' => 'New item was added successfully.', 'item-id' => $status['item_id']]);
        elseif ($status === false)
            self::sendError('Failed to add new item.');
        else
            self::sendError($status);
    }

    public function updateItem(): void
    {
        self::checkPermissions(['products' => [User::PERMISSION_MODIFY]]);

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
        self::checkPermissions(['products' => [User::PERMISSION_DELETE]]);

        $itemId = self::getParameter('item-id', dataType: 'int', isCompulsory: true);

        if (Items::deleteItem($itemId))
            self::sendSuccess('Item was deleted successfully.');
        else
            self::sendError('Failed to delete the item.');
    }

    public function getOrders(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $orderId = self::getParameter('order-id', dataType: 'int');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $addedDate = self::getParameter('added-date');
        $orderStatus = self::getParameter('order-status');

        $orders = Orders::getOrders($pageNumber, $orderId, $addedDate, $branchId, $orderStatus);
        self::sendSuccess(['orders' => $orders]);
    }

    public function addOrder(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_WRITE]]);

        $items = self::getParameter('items', dataType: 'array', isCompulsory: true);
        $totalPrice = self::getParameter('total-price', dataType: 'float');
        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        $comments = self::getParameter('customer-comments');

        $branchId = User::getUserBranchId(self::getUserId());
        if ($branchId == 0)
            $branchId = self::getParameter('branch-id', dataType: 'int');

        $status = Orders::addNewOrder($items, $customerId, $totalPrice, $branchId, $comments);
        if (is_array($status))
            self::sendSuccess(['message' => 'New order added successfully.', 'order-id' => $status['order_id']]);
        elseif ($status === false)
            self::sendError("Failed to add new order.");
        else
            self::sendError($status);
    }

    public function updateOrder(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_MODIFY]]);

        $orderId = self::getParameter('order-id', dataType: 'int', isCompulsory: true);
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $customerId = self::getParameter('customer-id', dataType: 'int');
        $status = self::getParameter('order-status');
        $items = self::getParameter('items', dataType: 'array');
        $totalPrice = self::getParameter('total-price', dataType: 'float');

        $status = Orders::updateOrder($orderId, $branchId, $status, $customerId, $items, $totalPrice);
        if ($status === true)
            self::sendSuccess("Order updated successfully.");
        elseif ($status === false)
            self::sendError("Failed to update the order.");
        else
            self::sendError($status);
    }

    public function deleteOrder(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_DELETE]]);

        $orderId = self::getParameter('order-id', dataType: 'int', isCompulsory: true);

        if (Orders::deleteOrder($orderId))
            self::sendSuccess("Order deleted successfully.");
        else
            self::sendError('Failed to delete the order.');
    }

    public function getOrderCount(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_READ]]);

        $branchId = User::getUserBranchId(self::getUserId());
        if ($branchId == 0)
            $branchId = self::getParameter('branch-id', dataType: 'int', isCompulsory: true);
        $noOfDaysBackward = self::getParameter('no-days-backward', defaultValue: 7, dataType: 'int');

        self::sendSuccess(['order-counts' => Orders::getOrderCount($branchId, $noOfDaysBackward)]);
    }

    public function getOrderStatusMessages(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_READ]]);

        self::sendSuccess(['status-messages' => Orders::getStatusMessages()]);
    }

    public function getPayments(): void
    {
        self::checkPermissions(['payments' => [User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $orderId = self::getParameter('order-id', dataType: 'int');
        $paymentId = self::getParameter('payment-id', dataType: 'int');
        $paidDate = self::getParameter('paid-date');

        self::sendSuccess(["payments" => Payments::getPayments($pageNumber, $paymentId, $orderId, $paidDate)]);
    }

    public function addPayment(): void
    {
        self::checkPermissions(['payments' => [User::PERMISSION_WRITE]]);

        $orderId = self::getParameter('order-id', dataType: 'int', isCompulsory: true);
        $paidAmount = self::getParameter('paid-amount', dataType: 'float', isCompulsory: true);
        $paidDate = self::getParameter('paid-date');

        $status = Payments::addPayment($orderId, $paidAmount, $paidDate);
        if (is_array($status))
            self::sendSuccess(['message' => 'Payment added successfully.', 'payment-id' => $status['payment_id']]);
        elseif ($status === false)
            self::sendError('Failed to add payment.');
        else
            self::sendError($status);
    }

    public function updatePayment(): void
    {
        self::checkPermissions(['payments' => [User::PERMISSION_MODIFY]]);

        $paymentId = self::getParameter('payment-id', dataType: 'int', isCompulsory: true);
        $refunded = self::getParameter('refunded', dataType: 'bool', isCompulsory: true);

        if (Payments::updatePayment($paymentId, $refunded))
            self::sendSuccess("Updated the payment details.");
        else
            self::sendError("Failed to update the payment details.");
    }

    public function deletePayment(): void
    {
        self::checkPermissions(['payments' => [User::PERMISSION_DELETE]]);

        $paymentId = self::getParameter('payment-id', dataType: 'int', isCompulsory: true);

        if (Payments::deletePayment($paymentId))
            self::sendSuccess("Deleted the payment successfully.");
        else
            self::sendError("Failed to delete the payment.");
    }

    public function addTax(): void
    {
        self::checkPermissions(['tax' => [User::PERMISSION_WRITE]]);

        $name = self::getParameter('tax-name', isCompulsory: true);
        $description = self::getParameter('description');
        $rate = self::getParameter('tax-rate', dataType: 'float', isCompulsory: true);

        $taxId = Taxes::createTax($name, $rate, $description);
        if (is_array($taxId))
            self::sendSuccess($taxId);
        else
            self::sendError($taxId);
    }

    public function getTaxes(): void
    {
        self::checkPermissions(['tax' => [User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $taxId = self::getParameter('tax-id', dataType: 'int');
        $name = self::getParameter('tax-name');
        $description = self::getParameter('description');
        $rateMin = self::getParameter('rate-min', dataType: 'float');
        $rateMax = self::getParameter('rate-max', dataType: 'float');

        $data = Taxes::getTaxes($pageNumber, $taxId, $name, $description, $rateMin, $rateMax);
        self::sendSuccess(['taxes' => $data]);
    }

    public function updateTax(): void
    {
        self::checkPermissions(['tax' => [User::PERMISSION_MODIFY]]);

        $taxId = self::getParameter('tax-id', dataType: 'int', isCompulsory: true);
        $name = self::getParameter('tax-name');
        $description = self::getParameter('description');
        $rate = self::getParameter('tax-rate', dataType: 'float');

        $status = Taxes::updateTax($taxId, $name, $description, $rate);
        if ($status === true)
            self::sendSuccess('Tax details were updated successfully.');
        elseif ($status === false)
            self::sendError('Failed to update details on the database.');
        else
            self::sendError($status);
    }

    public function deleteTax(): void
    {
        self::checkPermissions(['tax' => [User::PERMISSION_DELETE]]);

        $taxId = self::getParameter('tax-id', dataType: 'int', isCompulsory: true);

        $status = Taxes::deleteTax($taxId);
        if (is_string($status))
            self::sendError($status);
        elseif ($status)
            self::sendSuccess('Tax details were removed successfully.');
        else
            self::sendError('Failed to remove the tax details.');
    }

    public function getFinancialAccounts(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $name = self::getParameter('account-name');
        $code = self::getParameter('account-code');
        $type = self::getParameter('account-type');
        $taxId = self::getParameter('tax-id', dataType: 'int');
        $description = self::getParameter('description');

        $data = Accounting::getAccounts($pageNumber, $name, $code, $type, $description, $taxId);
        self::sendSuccess(['financial-accounts' => $data]);
    }

    public function addFinancialAccount(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_WRITE]]);

        $name = self::getParameter('account-name', isCompulsory: true);
        $code = self::getParameter('account-code', isCompulsory: true);
        $type = self::getParameter('account-type', isCompulsory: true);
        $taxId = self::getParameter('tax-id', dataType: 'int', isCompulsory: true);
        $description = self::getParameter('description');

        $status = Accounting::createAccount($name, $code, $type, $taxId, $description);
        if (is_string($status))
            self::sendError($status);
        else
            self::sendSuccess($status);
    }

    public function updateFinancialAccount(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_MODIFY]]);

        $accountId = self::getParameter('account-id', dataType: 'int', isCompulsory: true);

        self::sendError('Not implemented yet.');
    }

    public function deleteFinancialAccount(): void
    {
//        self::checkPermissions(['financial_accounts'=>[User::PERMISSION_DELETE]]);

        $accountId = self::getParameter('account-id', dataType: 'int', isCompulsory: true);

        $status = Accounting::deleteAccount($accountId);
        if (is_string($status))
            self::sendError($status);
        self::sendSuccess('Successfully removed the account.');
    }

    public function getReport(): void
    {
        (new Reports())->generatePdf();
    }

    // Server Status Functions

    public function getRealtimePerformanceMetrics(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $ram = ServerMetrics::getMemoryUsage();
        $cpu = ServerMetrics::getCpuUsage();

        self::sendSuccess(['ram-usage' => $ram, 'cpu-load' => $cpu]);
    }

    public function getMaintenanceStatus(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        self::sendSuccess(['maintenance-mode' => (new MigrationManager())->isInMaintenanceMode()]);
    }

    public function setMaintenanceStatus(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $enable = self::getParameter('enable', dataType: 'bool', isCompulsory: true);

        (new MigrationManager())->addMaintenanceMode($enable);
        if ($enable)
            self::sendSuccess("Maintenance mode enabled.");
        else
            self::sendSuccess('Maintenance mode disabled.');
    }

    public function getMigrations(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $migrations = (new MigrationManager())->getAvailableMigrations(true);
        self::sendSuccess(['available-migrations' => $migrations]);
    }

    public function getAppliedMigrations(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $migrationName = self::getParameter('migration-name');
        $appliedTime = self::getParameter('applied-time');
        $status = self::getParameter('status', dataType: 'bool');

        self::sendSuccess(['applied-migrations' => (new MigrationManager())
            ->getCompletedMigrations($pageNumber, $migrationName, $appliedTime, $status)]);
    }

    public function attemptMigration(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $migrationName = self::getParameter('migration-name', isCompulsory: true);
        $force = self::getParameter('force-run', defaultValue: false, dataType: 'bool');

        $manager = new MigrationManager();
        $status = $manager->startMigration($migrationName, $force);
        if ($status === true)
            self::sendSuccess("Successfully ran the migration.");
        elseif ($status === false)
            self::sendError("Failed to run the migration.");
        else
            self::sendError($status);
    }

    public function getMigrationToken(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        self::sendSuccess(['token' => (new MigrationManager())->getMigrationAuthToken()]);
    }

    public function blockMigrationToken(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $token = self::getParameter('token', isCompulsory: true);

        $status = (new MigrationManager())->blockMigrationAuthToken($token);
        if ($status === true)
            self::sendSuccess('Successfully blocked the token.');
        elseif ($status === false)
            self::sendError('Failed to block the token');
        else
            self::sendError($status);
    }

    public function validateMigrationToken(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $token = self::getParameter('token', isCompulsory: true);

        if ((new MigrationManager())->validateMigrationAuthToken($token))
            self::sendSuccess('Token is valid.');
        else {
            self::sendError('Token is expired.');
        }
    }


    /**
     * Check whether requests are coming from authorized users. If not send "401" unauthorized error message.
     * @param array $permissions Array of permissions needed. [roleGroup => [permissions], ...]
     */
    private static function checkPermissions(array $permissions = [], bool $onlyServerAdmins = false): void
    {
        preg_match('/Bearer\s(\S+)/', self::getAuthorizationHeader(), $matches);

        if (!$matches || !Authorization::isValidToken($matches[1])) {
            self::sendResponse(self::STATUS_CODE_FORBIDDEN, self::STATUS_MSG_FORBIDDEN,
                ['message' => 'You are not authorized to perform this action.']);
            exit();
        }

        $permitted = true;

        $userRole = User::getUserRole(self::getUserId());

        if (!$permissions && !$onlyServerAdmins)
            return;

        if ($userRole == User::ROLE_SUPER_ADMINISTRATOR)
            return;

        if (!$onlyServerAdmins) {
            $permissionGroups = array_keys(User::PERMISSIONS);
            foreach ($permissions as $group => $permissions_) {
                if (!in_array($group, $permissionGroups)) {
                    $permitted = false;
                    break;
                }
                foreach ($permissions_ as $item) {
                    if (!in_array($item, User::PERMISSIONS[$group])) {
                        $permitted = false;
                        break;
                    }
                }
            }
        } else
            $permitted = false;

        if (!$permitted) {
            self::sendResponse(self::STATUS_CODE_FORBIDDEN, self::STATUS_MSG_FORBIDDEN,
                ['message' => 'You are not authorized to perform this action.']);
        }
        exit();
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
        if (isset($_SERVER['HTTP_X_ADMINISTRATOR_TOKEN'])) {
            if ((new MigrationManager())->validateMigrationAuthToken($_SERVER['HTTP_X_ADMINISTRATOR_TOKEN']))
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