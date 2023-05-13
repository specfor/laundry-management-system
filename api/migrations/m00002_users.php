<?php

use LogicLeap\PhpServerCore\MigrationScheme;
use LogicLeap\StockManagement\models\User;

class m00002_users extends MigrationScheme
{
    /** Database name */
    protected const DB_NAME = 'laundry_database';

    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
                    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    username varchar(255) NOT NULL,
                    email varchar(255),
                    firstname varchar(255),
                    lastname varchar(255),
                    password varchar(255) NOT NULL,
                    role int(5) NOT NULL,
                    branch_id int(5)
                    )";

        $passwordHash = User::generatePasswordHash('rlsjp6)rg_34_)(23as');
        $sql2 = "INSERT INTO users (username, email, firstname, lastname, password, role)
                 select 'admin_{342365(_)08',null, null, null, '$passwordHash', 0
                 where not exists(select username from users where username='admin_{342365(_)08')";

        try {
            self::$pdo->exec($sql);
            self::$pdo->exec($sql2);
            return true;
        }catch (Exception){
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "DROP TABLE IF EXISTS users";
        if (!self::$pdo->exec($sql))
            return false;
        return true;
    }
}