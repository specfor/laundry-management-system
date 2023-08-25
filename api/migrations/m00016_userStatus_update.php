<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00016_userStatus_update extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return false;
    }

    public static function up(): bool
    {
        $sql = "alter table user_status drop primary key;
                alter table user_status add column id int auto_increment primary key;
                alter table user_status change last_active logged_at datetime not null;";
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