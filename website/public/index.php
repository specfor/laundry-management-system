<?php

use Dotenv\Dotenv;
use LogicLeap\StockManagement\controllers\SiteController;
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
        "dbName" => $_ENV['DB_NAME']
    ],
    'ExceptionHandler' => [SiteController::class, 'errorHandler']

];

$app = new Application($config);

// web routes
$app->router->addGetRoute('/', [SiteController::class, 'login']);
$app->router->addGetRoute('/dashboard', [SiteController::class, 'dashboard']);
$app->router->addGetRoute('/dashboard/branches', [SiteController::class, 'getBranches']);
$app->router->addGetRoute('/dashboard/employees', [SiteController::class, 'getEmployees']);
$app->router->addGetRoute('/dashboard/payments', [SiteController::class, 'getPayments']);
$app->router->addGetRoute('/dashboard/users', [SiteController::class, 'getUsers']);
$app->router->addGetRoute('/dashboard/products', [SiteController::class, 'getProducts']);
$app->router->addGetRoute('/dashboard/customers', [SiteController::class, 'getCustomers']);

$app->run();
