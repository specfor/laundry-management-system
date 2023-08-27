<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00018_generalLedger_addRecordAddedDate extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return false;
    }

    public static function up(): bool
    {
        $sql = "alter table general_ledger add column created_at int not null";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}