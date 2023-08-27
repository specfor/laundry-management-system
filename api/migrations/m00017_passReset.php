<?php
use LogicLeap\PhpServerCore\MigrationScheme;

class m00017_passReset extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "CREATE TABLE reset_pass_tokens (
                    id int auto_increment not null primary key,
                    user_id int not null,
                    token varchar(255) not null,
                    expire_at datetime not null,
                    used bool not null,
                    valid bool not null
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
        $sql = "DROP TABLE if exists reset_pass_tokens";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}