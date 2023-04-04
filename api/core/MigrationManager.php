<?php

namespace LogicLeap\StockManagement\core;

use DateTime;
use PDO;

class MigrationManager
{
    private PDO $pdo;

    private static bool $removeMaintenanceModeOnCompletion;
    private static string $migrationFolderPath;

    /**
     * If this file in this path exits, then website will go to maintenance mode
     */
    private static string $maintenanceModeFilePath;

    private static array $migrations = [];

    public function __construct(bool $removeMaintenanceModeOnCompletion = false)
    {
        $this->pdo = Application::$app->db->pdo;

        // Removes migration lock file so other requests will not run this again.
        $migrationModeFilePath = Application::$ROOT_DIR . "/migrationLock.lock";
        unlink($migrationModeFilePath);

        self::$removeMaintenanceModeOnCompletion = $removeMaintenanceModeOnCompletion;
        self::$maintenanceModeFilePath = Application::$ROOT_DIR . "/maintenanceLock.lock";
        self::$migrationFolderPath = Application::$ROOT_DIR . "/migrations";
        $f = fopen(self::$maintenanceModeFilePath, 'w');
        fclose($f);
        self::$migrations = scandir(self::$migrationFolderPath);

        // Removing '.' and '..' from file array
        unset(self::$migrations[0]);
        unset(self::$migrations[1]);
    }

    private function markCompletedMigration(string $migrationName, bool $success): void
    {
        $time = new DateTime("now");
        $time = $time->format("Y-m-d H:i:s");
        $sql = "INSERT INTO migrations (migration_name, time, status) VALUES (?, '$time', $success)";
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $migrationName);
        $statement->execute();
    }

    public function startMigration(): void
    {
        $i = 1;
        foreach (self::$migrations as $migration) {
            echo $i;
            $i++;
            require_once Application::$ROOT_DIR . "/migrations/" . $migration;
            $className = explode(".", $migration)[0];
            $mig = new $className;
            if ($mig->up()) {
                $this->markCompletedMigration($className, true);
            } else {
                $this->markCompletedMigration($className, false);
                break;
            }
        }
    }

    public function __destruct()
    {
        if (self::$removeMaintenanceModeOnCompletion)
            unlink(self::$maintenanceModeFilePath);
    }
}