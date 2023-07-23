<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00011_userRoles extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_roles (
                    role_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    name varchar(50) NOT NULL,
                    description varchar(255),
                    permissions varchar(1000) NOT NULL,
                    locked bool NOT NULL
                )";

        $sql2 = "INSERT INTO user_roles (name, permissions, locked)
                 select 'administrator', '{\"all\": true}', true
                 where not exists(select name from user_roles where name='administrator')";
    
        try {
            self::$pdo->exec($sql);
            self::$pdo->exec($sql2);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "DROP TABLE IF EXISTS user_roles";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}