<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00014_taxes extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS taxes (
                    tax_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    name varchar(255) NOT NULL,
                    description varchar(500),
                    tax_rate decimal(8,4) NOT NULL,
                    deleted bool NOT NULL,
                    locked bool NOT NULL
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
        $sql = "DROP TABLE IF EXISTS taxes";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}