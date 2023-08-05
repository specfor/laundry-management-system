<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00013_generalLedger extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS general_ledger (
                    record_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    account_id int NOT NULL,
                    reference varchar(255),
                    description varchar(500),
                    credit decimal(20,4),
                    debit decimal(20,4),
                    tax decimal(20,4),
                    timestamp varchar(15) NOT NULL
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
        $sql = "DROP TABLE IF EXISTS general_ledger";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}