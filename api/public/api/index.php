<?php

use Dotenv\Dotenv;
use LogicLeap\PhpServerCore\Application;
use LogicLeap\StockManagement\controllers\v1\accounting\AccountTotalsController;
use LogicLeap\StockManagement\controllers\v1\accounting\FinancialAccountController;
use LogicLeap\StockManagement\controllers\v1\accounting\InvoiceController;
use LogicLeap\StockManagement\controllers\v1\accounting\LedgerRecordController;
use LogicLeap\StockManagement\controllers\v1\accounting\TaxController;
use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\controllers\v1\ReportController;
use LogicLeap\StockManagement\controllers\v1\server_admin\MigrationsController;
use LogicLeap\StockManagement\controllers\v1\server_admin\PackageInitController;
use LogicLeap\StockManagement\controllers\v1\server_admin\ServerAdminPanelController;
use LogicLeap\StockManagement\controllers\v1\stock_management\BranchController;
use LogicLeap\StockManagement\controllers\v1\stock_management\CustomerController;
use LogicLeap\StockManagement\controllers\v1\stock_management\EmployeeController;
use LogicLeap\StockManagement\controllers\v1\stock_management\ItemController;
use LogicLeap\StockManagement\controllers\v1\stock_management\OrderController;
use LogicLeap\StockManagement\controllers\v1\stock_management\PaymentController;
use LogicLeap\StockManagement\controllers\v1\stock_management\PriceCategoryController;
use LogicLeap\StockManagement\controllers\v1\user_management\AuthController;
use LogicLeap\StockManagement\controllers\v1\user_management\UserController;
use LogicLeap\StockManagement\controllers\v1\user_management\UserRoleController;

require_once __DIR__ . '/../../vendor/autoload.php';

//Loading database details to environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

define("SYSTEM_DOMAIN", $_ENV['SYSTEM_DOMAIN']);

$config = [
    'rootPath' => dirname(__DIR__,2),
    'db' => [
        "servername" => $_ENV['DB_SERVERNAME'],
        "username" => $_ENV['DB_USERNAME'],
        "password" => $_ENV['DB_PASSWORD'],
        "dbName" => $_ENV['DB_NAME']
    ],
    'ExceptionHandler' => [Controller::class, 'errorHandler']
];

$app = new Application($config);
// API routes
$app->router->addPostRoute('/api/v1/login', [AuthController::class, 'login']);
$app->router->addGetRoute('/api/v1/whoami', [AuthController::class, 'whoAmI']);
$app->router->addGetRoute('/api/v1/users', [UserController::class, 'getUsers']);
$app->router->addPostRoute('/api/v1/users/add', [UserController::class, 'addUser']);
$app->router->addPostRoute('/api/v1/users/update', [UserController::class, 'updateUser']);
$app->router->addPostRoute('/api/v1/users/delete', [UserController::class, 'deleteUser']);
$app->router->addGetRoute('/api/v1/customers', [CustomerController::class, 'getCustomers']);
$app->router->addPostRoute('/api/v1/customers/add', [CustomerController::class, 'addCustomer']);
$app->router->addPostRoute('/api/v1/customers/update', [CustomerController::class, 'updateCustomer']);
$app->router->addPostRoute('/api/v1/customers/delete', [CustomerController::class, 'deleteCustomer']);
$app->router->addGetRoute('/api/v1/branches', [BranchController::class, 'getBranches']);
$app->router->addPostRoute('/api/v1/branches/add', [BranchController::class, 'addBranch']);
$app->router->addPostRoute('/api/v1/branches/update', [BranchController::class, 'updateBranch']);
$app->router->addPostRoute('/api/v1/branches/delete', [BranchController::class, 'deleteBranch']);
$app->router->addGetRoute('/api/v1/employees', [EmployeeController::class, 'getEmployees']);
$app->router->addPostRoute('/api/v1/employees/add', [EmployeeController::class, 'addEmployee']);
$app->router->addPostRoute('/api/v1/employees/update', [EmployeeController::class, 'updateEmployee']);
$app->router->addPostRoute('/api/v1/employees/delete', [EmployeeController::class, 'deleteEmployee']);
$app->router->addGetRoute('/api/v1/category', [PriceCategoryController::class, 'getPriceCategories']);
$app->router->addPostRoute('/api/v1/category/add', [PriceCategoryController::class, 'addPriceCategory']);
$app->router->addPostRoute('/api/v1/category/update', [PriceCategoryController::class, 'updatePriceCategory']);
$app->router->addPostRoute('/api/v1/category/delete', [PriceCategoryController::class, 'deletePriceCategory']);
$app->router->addGetRoute('/api/v1/items', [ItemController::class, 'getItems']);
$app->router->addPostRoute('/api/v1/items/add', [ItemController::class, 'addItem']);
$app->router->addPostRoute('/api/v1/items/update', [ItemController::class, 'updateItem']);
$app->router->addPostRoute('/api/v1/items/delete', [ItemController::class, 'deleteItem']);
$app->router->addGetRoute('/api/v1/orders', [OrderController::class, 'getOrders']);
$app->router->addGetRoute('/api/v1/orderCount', [OrderController::class, 'getOrderCount']);
$app->router->addPostRoute('/api/v1/orders/add', [OrderController::class, 'addOrder']);
$app->router->addPostRoute('/api/v1/orders/update', [OrderController::class, 'updateOrder']);
$app->router->addPostRoute('/api/v1/orders/delete', [OrderController::class, 'deleteOrder']);
$app->router->addGetRoute('/api/v1/payments', [PaymentController::class, 'getPayments']);
$app->router->addPostRoute('/api/v1/payments/add', [PaymentController::class, 'addPayment']);
$app->router->addPostRoute('/api/v1/payments/update', [PaymentController::class, 'updatePayment']);
$app->router->addPostRoute('/api/v1/payments/delete', [PaymentController::class, 'deletePayment']);
$app->router->addGetRoute('/api/v1/reports', [ReportController::class, 'getReport']);
$app->router->addGetRoute('/api/v1/user-roles/permissions', [UserRoleController::class, 'getAvailablePermissions']);
$app->router->addGetRoute('/api/v1/user-roles', [UserRoleController::class, 'getUserRoles']);
$app->router->addPostRoute('/api/v1/user-roles/add', [UserRoleController::class, 'addUserRole']);
$app->router->addPostRoute('/api/v1/user-roles/update', [UserRoleController::class, 'updateUserRole']);
$app->router->addPostRoute('/api/v1/user-roles/delete', [UserRoleController::class, 'deleteUserRole']);
$app->router->addGetRoute('/api/v1/users/login-history', [UserController::class, 'getUserLoginHistory']);
$app->router->addPostRoute('/api/v1/login/send-reset-mail', [UserController::class, 'sendPasswordResetLink']);
$app->router->addPostRoute('/api/v1/login/reset-password/new-password', [UserController::class, 'resetPassword']);
$app->router->addPostRoute('/api/v1/login/reset-password/check-token', [UserController::class, 'checkPassResetTokenValidity']);
$app->router->addGetRoute('/api/v1/profile', [UserController::class, 'getProfile']);
$app->router->addPostRoute('/api/v1/profile/update-password', [UserController::class, 'updateCurrentPassword']);
$app->router->addGetRoute('/api/v1/profile/images/$', [UserController::class, 'getProfilePicture']);
$app->router->addPostRoute('/api/v1/profile/profile-picture', [UserController::class, 'uploadProfilePicture']);
$app->router->addPostRoute('/api/v1/users/update/profile-picture', [UserController::class, 'uploadUserProfilePicture']);
$app->router->addPostRoute('/api/v1/users/uploads', [UserController::class, 'uploadFiles']);
$app->router->addGetRoute('/api/v1/users/uploads', [UserController::class, 'getUploadFileList']);
$app->router->addGetRoute('/api/v1/users/uploads/$', [UserController::class, 'getFile']);
$app->router->addPostRoute('/api/v1/users/uploads/delete', [UserController::class, 'deleteFile']);

// Accounting routes
$app->router->addGetRoute('/api/v1/taxes', [TaxController::class, 'getTaxes']);
$app->router->addPostRoute('/api/v1/taxes/add', [TaxController::class, 'addTax']);
$app->router->addPostRoute('/api/v1/taxes/update', [TaxController::class, 'updateTax']);
$app->router->addPostRoute('/api/v1/taxes/delete', [TaxController::class, 'deleteTax']);
$app->router->addGetRoute('/api/v1/financial-accounts', [FinancialAccountController::class, 'getFinancialAccounts']);
$app->router->addPostRoute('/api/v1/financial-accounts/add', [FinancialAccountController::class, 'addFinancialAccount']);
$app->router->addPostRoute('/api/v1/financial-accounts/update', [FinancialAccountController::class, 'updateFinancialAccount']);
$app->router->addPostRoute('/api/v1/financial-accounts/delete', [FinancialAccountController::class, 'deleteFinancialAccount']);
$app->router->addGetRoute('/api/v1/financial-account-types', [FinancialAccountController::class, 'getAccountTypes']);
$app->router->addGetRoute('/api/v1/general-ledger', [LedgerRecordController::class, 'getLedgerRecords']);
$app->router->addPostRoute('/api/v1/general-ledger/add', [LedgerRecordController::class, 'addLedgerRecord']);
$app->router->addGetRoute('/api/v1/account-totals', [AccountTotalsController::class, 'calculateTotals']);
$app->router->addGetRoute('/api/v1/account-totals/day-basis', [AccountTotalsController::class, 'getTotalByDate']);
$app->router->addGetRoute('/api/v1/account-totals/month-basis', [AccountTotalsController::class, 'getTotalByMonth']);
$app->router->addGetRoute('/api/v1/account-totals/year-basis', [AccountTotalsController::class, 'getTotalByYear']);
$app->router->addPostRoute('/api/v1/invoices/new', [InvoiceController::class, 'sendInvoice']);

// Super admin routes
$app->router->addGetRoute('/api/v1/realtime-metrics', [ServerAdminPanelController::class, 'getRealtimePerformanceMetrics']);
$app->router->addGetRoute('/api/v1/server-manager/dashboard', [ServerAdminPanelController::class, 'getAdminPanel']);
$app->router->addGetRoute('/api/v1/server-manager/maintenanceMode', [ServerAdminPanelController::class, 'getMaintenanceStatus']);
$app->router->addPostRoute('/api/v1/server-manager/maintenanceMode', [ServerAdminPanelController::class, 'setMaintenanceStatus']);
$app->router->addPostRoute('/api/v1/server-manager/login', [ServerAdminPanelController::class, 'adminLogin']);
$app->router->addGetRoute('/api/v1/server-manager/migrations', [MigrationsController::class, 'getMigrations']);
$app->router->addGetRoute('/api/v1/server-manager/migrations/applied', [MigrationsController::class, 'getAppliedMigrations']);
$app->router->addPostRoute('/api/v1/server-manager/migrations/run', [MigrationsController::class, 'attemptMigration']);
$app->router->addGetRoute('/api/v1/server-manager/migration-token', [MigrationsController::class, 'getMigrationToken']);
$app->router->addPostRoute('/api/v1/server-manager/migration-token/block', [MigrationsController::class, 'blockMigrationToken']);
$app->router->addPostRoute('/api/v1/server-manager/migration-token/validate', [MigrationsController::class, 'validateMigrationToken']);
$app->router->addPostRoute('/api/v1/server-manager/init/accounting', [PackageInitController::class, 'intAccountingPackage']);

$app->run();
