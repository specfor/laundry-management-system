<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00005_branches extends MigrationScheme
{

    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS branches (
                    branch_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    name varchar(255) NOT NULL ,
                    address varchar(255),
                    manager_id int(5),                    
                    phone_num varchar(20),
                    blocked bool NOT NULL                  
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
        $sql = "DROP TABLE IF EXISTS branches";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}