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

        $sql3 = "CREATE TABLE IF NOT EXISTS users (
                    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    username varchar(255) NOT NULL,
                    email varchar(255),
                    firstname varchar(255),
                    lastname varchar(255),
                    password varchar(255) NOT NULL,
                    role int(5) NOT NULL
                    )";

        $passwordHash = User::generatePasswordHash('rlsjp6)rg_34_)(23as');
        $sql4 = "INSERT INTO users (username, email, firstname, lastname, password, role) VALUES ('admin_{342365(_)08', 
                                                                null, null, null, '$passwordHash', 0)";

        try {
            self::$pdo->exec($sql1);
            self::$pdo->exec($sql2);
            self::$pdo->exec($sql3);
            self::$pdo->exec($sql4);
            return true;
        }catch (Exception){
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "DROP TABLE IF EXISTS users, migrations";
        if (!self::$pdo->exec($sql))
            return false;
        return true;
    }
}