<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00015_accountTotals extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS financial_account_totals (
                    account_id int NOT NULL PRIMARY KEY,
                    date date NOT NULL PRIMARY KEY,
                    credit decimal(30,4),
                    debit decimal(30,4),
                    until_ledger_rec_id int NOT NULL,
                    CONSTRAINT prime_acc_totals PRIMARY KEY (account_id, date)
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
        $sql = "DROP TABLE IF EXISTS financial_account_totals";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}