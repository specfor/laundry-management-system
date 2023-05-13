<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00010_payments extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS payments (
                    payment_id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
                    order_id int NOT NULL,
                    paid_amount float NOT NULL,
                    paid_date date NOT NULL,
                    refunded bool default false NOT NULL  
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
        $sql = "DROP TABLE IF EXISTS payments";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}