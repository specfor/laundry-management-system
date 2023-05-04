<?php

use LogicLeap\StockManagement\core\MigrationScheme;

class m00009_orders extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS orders (
                    order_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    items varchar(300) NOT NULL,
                    total_price float NOT NULL,
                    added_date date NOT NULL, 
                    branch_id int NOT NULL,
                    status int NOT NULL    
                )";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "DROP TABLE IF EXISTS orders";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}