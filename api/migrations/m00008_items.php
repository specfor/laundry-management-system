<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00008_items extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS items (
                    item_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    name varchar(255) NOT NULL,
                    price decimal(15,3),
                    category_ids varchar(255),
                    blocked bool NOT NULL     
                )";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public static function down(): bool
    {
        $sql = "DROP TABLE IF EXISTS items";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}