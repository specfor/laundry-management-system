<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00020_users_addUserProfilePhoto extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return false;
    }

    public static function up(): bool
    {
        $sql = "alter table users add profile_pic varchar(500);
                alter table users add documents json;";
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