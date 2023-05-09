<?php

namespace LogicLeap\StockManagement\core;

use DateInterval;
use DateTime;
use Exception;
use PDO;

class MigrationManager
{
    private static PDO $pdo;
    private const TOKEN_EXPIRE_TIME = 43200; // In seconds.

    private static bool $removeMaintenanceModeOnCompletion;
    private static string $migrationFolderPath;

    /**
     * If this file in this path exits, then website will go to maintenance mode
     */
    private static string $maintenanceModeFilePath;

    public function __construct(bool $removeMaintenanceModeOnCompletion = false)
    {
        self::$pdo = Application::$app->db->pdo;
        self::$maintenanceModeFilePath = Application::$ROOT_DIR . "/maintenanceLock.lock";
        self::$migrationFolderPath = Application::$ROOT_DIR . "/migrations";

        // Removes migration lock file so other requests will not run this again.
        $migrationModeFilePath = Application::$ROOT_DIR . "/migrationLock.lock";
        if (is_file($migrationModeFilePath)) {
            unlink($migrationModeFilePath);
            self::addMaintenanceMode();
        }

        self::$removeMaintenanceModeOnCompletion = $removeMaintenanceModeOnCompletion;
    }

    private function markCompletedMigration(string $migrationName, bool $success): void
    {
        $time = new DateTime("now");
        $time = $time->format("Y-m-d H:i:s");
        $sql = "INSERT INTO migrations (migration_name, time, status) VALUES (?, '$time', '$success')";
        $statement = self::$pdo->prepare($sql);
        $statement->bindValue(1, $migrationName);
        $statement->execute();
    }

    public function getCompletedMigrations(int  $pageNumber = 0, string $migrationName = null, string $time = null,
                                           bool $status = null, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($migrationName) {
            $filters[] = "migration_name=:name";
            $placeholders['name'] = $migrationName;
        }
        if ($time) {
            $filters[] = "time=:time";
            $placeholders['time'] = $migrationName;
        }
        if ($status)
            $filters[] = "status=$status";

        if ($filters)
            $condition = implode(' AND ', $filters);
        $sql = "SELECT * FROM migrations";
        if (isset($condition))
            $sql .= " WHERE $condition";
        $sql .= " ORDER BY id desc LIMIT $startingIndex, $limit";

        $statement = self::$pdo->prepare($sql);
        if ($placeholders) {
            foreach ($placeholders as $key => $value) {
                $statement->bindValue($key, $value);
            }
        }
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['status'] = boolval($data[$i]['status']);
        }
        return $data;
    }

    public function startMigration(string $migrationNameToRun = null, bool $force = false): bool|string
    {
        $migrations = $this->getAvailableMigrations();

        if ($migrationNameToRun) {
            if (!array_search($migrationNameToRun . ".php", $migrations))
                return "No migration named '$migrationNameToRun' was found.";
        }
        $ran = false;
        foreach ($migrations as $migration) {
            if ($ran)
                break;
            $migrationName = explode(".", $migration)[0];
            if ($migrationNameToRun)
                if ($migrationNameToRun != $migrationName)
                    continue;
            if (!$force)
                if ($this->isAppliedMigration($migrationName))
                    if ($migrationNameToRun)
                        return "Migration has already done.";
                    else
                        continue;

            require_once Application::$ROOT_DIR . "/migrations/" . $migration;
            $mig = new $migrationName;
            if ($mig->up()) {
                $this->markCompletedMigration($migrationName, true);
            } else {
                if ($mig->isReversible())
                    $mig->down();
                $this->markCompletedMigration($migrationName, false);
                return false;
            }
            $ran = true;
        }
        return true;
    }

    public function getMigrationAuthToken(): string
    {
        $token = SecureToken::generateToken();

        $now = new DateTime("now");
        $time = $now->format("Y-m-d H:i:s");
        $expTime = $now->add(DateInterval::createFromDateString(self::TOKEN_EXPIRE_TIME . ' seconds'));
        $expTime = $expTime->format('Y-m-d H:i:s');

        $sql = "INSERT INTO migration_tokens (token, time, exp_time) VALUES ('$token', '$time', '$expTime')";
        if (self::$pdo->exec($sql))
            return $token;
        else
            return "Failed to save token.";
    }

    public function validateMigrationAuthToken(string $token): bool
    {
        $sql = "SELECT time, exp_time FROM migration_tokens WHERE token=:token AND blocked=false";
        $statement = self::$pdo->prepare($sql);
        $statement->bindValue(":token", $token);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($data))
            return false;

        $now = new DateTime("now");
        $time = $now->format("Y-m-d H:i:s");
        if ($time < $data['exp_time'])
            return true;
        return false;
    }

    public function blockMigrationAuthToken(string $token): bool|string
    {
        $sql = "SELECT id FROM migration_tokens WHERE token=:token";
        $statement = self::$pdo->prepare($sql);
        $statement->bindValue(":token", $token);
        $statement->execute();
        if (empty($statement->fetch(PDO::FETCH_ASSOC)))
            return "Invalid token.";

        $sql = "UPDATE migration_tokens SET blocked=true WHERE token=:token";
        $statement = self::$pdo->prepare($sql);
        $statement->bindValue(":token", $token);
        if ($statement->execute())
            return true;
        return false;
    }


    public function addMaintenanceMode($enable = true): void
    {
        if ($enable) {
            $f = fopen(self::$maintenanceModeFilePath, 'w');
            fclose($f);
        } else {
            if (self::isInMaintenanceMode())
                unlink(self::$maintenanceModeFilePath);
        }
    }

    public function isInMaintenanceMode(): bool
    {
        return file_exists(self::$maintenanceModeFilePath);
    }

    public function getAvailableMigrations(bool $namesWithoutDotPHP = false): array
    {
        $migrations = scandir(self::$migrationFolderPath);

        for ($i = 0; $i < count($migrations); $i++) {
            if (is_dir(self::$migrationFolderPath . "/" . $migrations[$i])) {
                unset($migrations[$i]);
            }
        }
        $migrations = array_values($migrations);

        for ($i = 0; $i < count($migrations); $i++) {
            if ($namesWithoutDotPHP)
                $migrations[$i] = explode(".php", $migrations[$i])[0];
        }
        return $migrations;
    }

    private function isAppliedMigration(string $migrationName): bool
    {
        $sql = "SELECT id FROM migrations WHERE migration_name=? AND status=1";
        $statement = self::$pdo->prepare($sql);
        $statement->bindValue(1, $migrationName);
        try {
            $statement->execute();
        } catch (Exception) {
            return false;
        }
        if ($statement->fetch(PDO::FETCH_ASSOC))
            return true;
        return false;
    }

    public function __destruct()
    {
        if (self::$removeMaintenanceModeOnCompletion)
            unlink(self::$maintenanceModeFilePath);
    }
}