<?php

use LogicLeap\StockManagement\core\MigrationScheme;

class m00006_employees extends MigrationScheme
{

    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS employees (
                    employee_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    name varchar(255) NOT NULL ,
                    address varchar(255),
                    email varchar(255),
                    phone_num varchar(30),
                    branch_id int,
                    join_date date,
                    left_date date,
                    is_left bool NOT NULL                  
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
        $sql = "DROP TABLE IF EXISTS employees";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}