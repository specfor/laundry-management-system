<?php

use Dotenv\Dotenv;
use LogicLeap\StockManagement\controllers\ApiControllerV1;
use LogicLeap\PhpServerCore\Application;

require_once __DIR__ . '/../vendor/autoload.php';

//Loading database details to environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$config = [
    'rootPath' => dirname(__DIR__),
    'db' => [
        "servername" => $_ENV['DB_SERVERNAME'],
        "username" => $_ENV['DB_USERNAME'],
        "password" => $_ENV['DB_PASSWORD'],
        "dbName" => $_ENV['DB_NAME']
    ],
    'ExceptionHandler' => [ApiControllerV1::class, 'errorHandler']
];

$app = new Application($config);
// API routes
$app->router->addPostRoute('/api/v1/login', [ApiControllerV1::class, 'login']);
$app->router->addGetRoute('/api/v1/users', [ApiControllerV1::class, 'getUsers']);
$app->router->addPostRoute('/api/v1/users/add', [ApiControllerV1::class, 'addUser']);
$app->router->addPostRoute('/api/v1/users/update', [ApiControllerV1::class, 'updateUser']);
$app->router->addPostRoute('/api/v1/users/delete', [ApiControllerV1::class, 'deleteUser']);
$app->router->addGetRoute('/api/v1/customers', [ApiControllerV1::class, 'getCustomers']);
$app->router->addPostRoute('/api/v1/customers/add', [ApiControllerV1::class, 'addCustomer']);
$app->router->addPostRoute('/api/v1/customers/update', [ApiControllerV1::class, 'updateCustomer']);
$app->router->addPostRoute('/api/v1/customers/delete', [ApiControllerV1::class, 'deleteCustomer']);
$app->router->addGetRoute('/api/v1/branches', [ApiControllerV1::class, 'getBranches']);
$app->router->addPostRoute('/api/v1/branches/add', [ApiControllerV1::class, 'addBranch']);
$app->router->addPostRoute('/api/v1/branches/update', [ApiControllerV1::class, 'updateBranch']);
$app->router->addPostRoute('/api/v1/branches/delete', [ApiControllerV1::class, 'deleteBranch']);
$app->router->addGetRoute('/api/v1/employees', [ApiControllerV1::class, 'getEmployees']);
$app->router->addPostRoute('/api/v1/employees/add', [ApiControllerV1::class, 'addEmployee']);
$app->router->addPostRoute('/api/v1/employees/update', [ApiControllerV1::class, 'updateEmployee']);
$app->router->addPostRoute('/api/v1/employees/delete', [ApiControllerV1::class, 'deleteEmployee']);
$app->router->addGetRoute('/api/v1/category', [ApiControllerV1::class, 'getPriceCategories']);
$app->router->addPostRoute('/api/v1/category/add', [ApiControllerV1::class, 'addPriceCategory']);
$app->router->addPostRoute('/api/v1/category/update', [ApiControllerV1::class, 'updatePriceCategory']);
$app->router->addPostRoute('/api/v1/category/delete', [ApiControllerV1::class, 'deletePriceCategory']);
$app->router->addGetRoute('/api/v1/items', [ApiControllerV1::class, 'getItems']);
$app->router->addPostRoute('/api/v1/items/add', [ApiControllerV1::class, 'addItem']);
$app->router->addPostRoute('/api/v1/items/update', [ApiControllerV1::class, 'updateItem']);
$app->router->addPostRoute('/api/v1/items/delete', [ApiControllerV1::class, 'deleteItem']);
$app->router->addGetRoute('/api/v1/orders', [ApiControllerV1::class, 'getOrders']);
$app->router->addGetRoute('/api/v1/orders/status-messages', [ApiControllerV1::class, 'getOrderStatusMessages']);
$app->router->addPostRoute('/api/v1/orders/add', [ApiControllerV1::class, 'addOrder']);
$app->router->addPostRoute('/api/v1/orders/update', [ApiControllerV1::class, 'updateOrder']);
$app->router->addPostRoute('/api/v1/orders/delete', [ApiControllerV1::class, 'deleteOrder']);
$app->router->addGetRoute('/api/v1/payments', [ApiControllerV1::class, 'getPayments']);
$app->router->addPostRoute('/api/v1/payments/add', [ApiControllerV1::class, 'addPayment']);
$app->router->addPostRoute('/api/v1/payments/update', [ApiControllerV1::class, 'updatePayment']);
$app->router->addPostRoute('/api/v1/payments/delete', [ApiControllerV1::class, 'deletePayment']);

// Super admin routes
$app->router->addGetRoute('/api/v1/realtime-metrics', [ApiControllerV1::class, 'getRealtimePerformanceMetrics']);
$app->router->addGetRoute('/api/v1/server-manager/maintenanceMode', [ApiControllerV1::class, 'getMaintenanceStatus']);
$app->router->addPostRoute('/api/v1/server-manager/maintenanceMode', [ApiControllerV1::class, 'setMaintenanceStatus']);
$app->router->addGetRoute('/api/v1/server-manager/migrations', [ApiControllerV1::class, 'getMigrations']);
$app->router->addGetRoute('/api/v1/server-manager/migrations/applied', [ApiControllerV1::class, 'getAppliedMigrations']);
$app->router->addPostRoute('/api/v1/server-manager/migrations/run', [ApiControllerV1::class, 'attemptMigration']);
$app->router->addGetRoute('/api/v1/server-manager/migration-token', [ApiControllerV1::class, 'getMigrationToken']);
$app->router->addPostRoute('/api/v1/server-manager/migration-token/block', [ApiControllerV1::class, 'blockMigrationToken']);
$app->router->addPostRoute('/api/v1/server-manager/migration-token/validate', [ApiControllerV1::class, 'validateMigrationToken']);

$app->run();
