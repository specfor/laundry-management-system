<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00003_userStatus extends MigrationScheme
{

    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_status (
                    user_id int NOT NULL PRIMARY KEY,
                    auth_token varchar(255) NOT NULL, 
                    last_active datetime NOT NULL,
                    ip_addr varchar(30) NOT NULL,
                    exp_time datetime NOT NULL
                )";
        try {
            self::$pdo->exec($sql);
            return true;
        }catch (Exception) {
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "DROP TABLE IF EXISTS user_status";
        try {
            self::$pdo->exec($sql);
            return true;
        }catch (Exception){
            return false;
        }
    }
}