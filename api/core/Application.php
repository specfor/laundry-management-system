<?php

namespace LogicLeap\StockManagement\core;

use Exception;
use LogicLeap\StockManagement\models\API;

/**
 * Class Application
 */
class Application
{
    public static Application $app;
    public Database $db;
    public Router $router;
    public Request $request;

    public static string $ROOT_DIR;

    /**
     * If this file in this path exits, then website will go to maintenance mode
     */
    private static string $maintenanceModeFilePath;
    private static string $migrationModeFilePath;

    /**
     * Return an instance of Application
     * @param array $config parse a nested array of configurations.
     */
    public function __construct(array $config)
    {
        self::$app = $this;
        self::$ROOT_DIR = $config['rootPath'];
        self::$maintenanceModeFilePath = self::$ROOT_DIR . "/maintenanceLock.lock";
        self::$migrationModeFilePath = self::$ROOT_DIR . "/migrationLock.lock";


        $this->request = new Request();
        $this->router = new Router($this->request);
        $this->db = new Database($config['db']['servername'], $config['db']['username'], $config['db']['password'],
            $config['db']['dbName']);

        if (is_file(self::$migrationModeFilePath)) {
            $migrationManager = new MigrationManager();
            $migrationManager->startMigration();
        }

        // If in maintenance mode, maintenance page is displayed. Application exits.
        if (is_file(self::$maintenanceModeFilePath)) {
            API::sendResponse(API::STATUS_CODE_MAINTENANCE, API::STATUS_MSG_MAINTENANCE,
                ['error' => 'Server is under maintenance']);
            exit();
        }
    }

    /**
     * Start the application. Call to resolveRoute.
     * If any error occurred, call to render the relevant error page.
     */
    public function run(): void
    {
        try {
            $this->router->resolveRoute();
        } catch (Exception $e) {
            API::sendResponse($e->getCode(), $e->getMessage(), ['error' => $e->getMessage()]);
        }
    }
}