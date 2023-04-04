<?php

use LogicLeap\StockManagement\core\MigrationScheme;

class m00003_customers extends MigrationScheme
{

    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS customers (
                    customer_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    email varchar(255),
                    phone_num varchar(20),
                    firstname varchar(255),
                    lastname varchar(255),                    
                    address varchar(255)                    
                )";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "DROP TABLE IF EXISTS customers";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}