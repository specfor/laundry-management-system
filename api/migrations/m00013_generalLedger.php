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
                    narration varchar(255) NOT NULL,
                    body json NOT NULL,
                    tot_amount decimal(30,4),
                    date date NOT NULL
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