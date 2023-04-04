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
//$app->router->addPostRoute('/api/v1/register', [ApiControllerV1::class, 'register']);
$app->router->addPostRoute('/api/v1/customers/add', [ApiControllerV1::class, 'addCustomer']);

$app->run();
