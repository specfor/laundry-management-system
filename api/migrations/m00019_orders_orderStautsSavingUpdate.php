<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00019_orders_orderStautsSavingUpdate extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return false;
    }

    public static function up(): bool
    {
        $sql = "alter table orders change status status json not null";
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