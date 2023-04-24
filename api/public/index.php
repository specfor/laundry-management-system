<?php

use Dotenv\Dotenv;
use LogicLeap\StockManagement\controllers\ApiControllerV1;
use LogicLeap\StockManagement\core\Application;

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
        "dbName"=>$_ENV['DB_NAME']
    ]
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

$app->run();
