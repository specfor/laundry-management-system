<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00012_financialAccounts extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS financial_accounts (
                    account_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    name varchar(255) NOT NULL,
                    type varchar(50) NOT NULL,
                    code varchar(10) NOT NULL,
                    description varchar(255),
                    tax_id int NOT NULL,
                    archived bool NOT NULL,
                    deletable bool NOT NULL
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
        $sql = "DROP TABLE IF EXISTS financial_accounts";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}