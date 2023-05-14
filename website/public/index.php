<?php

use Dotenv\Dotenv;
use LogicLeap\StockManagement\controllers\SiteController;
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
    'ExceptionHandler' => [SiteController::class, 'errorHandler']

];

$app = new Application($config);

// web routes
$app->router->addGetRoute('/', [SiteController::class, 'login']);
$app->router->addGetRoute('/dashboard', [SiteController::class, 'dashboard']);
$app->router->addGetRoute('/branches', [SiteController::class, 'getBranches']);
$app->router->addGetRoute('/employees', [SiteController::class, 'getEmployees']);
$app->router->addGetRoute('/payments', [SiteController::class, 'getPayments']);
$app->router->addGetRoute('/users', [SiteController::class, 'getUsers']);
$app->router->addGetRoute('/products', [SiteController::class, 'getProducts']);
$app->router->addGetRoute('/customers', [SiteController::class, 'getCustomers']);
$app->router->addGetRoute('/orders', [SiteController::class, 'getOrders']);
$app->router->addGetRoute('/orders/new-order', [SiteController::class, 'newOrder']);
$app->router->addGetRoute('/orders/update-order', [SiteController::class, 'updateOrder']);
$app->router->addGetRoute('/orders/new-order/payment', [SiteController::class, 'finalizeOrder']);

// Super Admin Routes
$app->router->addGetRoute('/server-performance', [SiteController::class, 'getRealtimePerformanceMetrics']);
$app->router->addGetRoute('/server-manager', [SiteController::class, 'getServerManager']);

$app->run();
