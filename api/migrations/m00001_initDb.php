<?php

use LogicLeap\StockManagement\core\MigrationScheme;
use LogicLeap\StockManagement\models\User;

class m00001_initDb extends MigrationScheme
{
    /** Database name */
    protected const DB_NAME = 'laundry_database';

    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql1 = "CREATE DATABASE IF NOT EXISTS " . self::DB_NAME;

        $sql2 = "CREATE TABLE IF NOT EXISTS migrations (
                    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    migration_name varchar(256) NOT NULL,
                    time datetime NOT NULL,
                    status bool NOT NULL)";

        try {
            self::$pdo->exec($sql1);
            self::$pdo->exec($sql2);
            return true;
        }catch (Exception){
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "DROP TABLE IF EXISTS migrations";
        if (!self::$pdo->exec($sql))
            return false;
        return true;
    }
}