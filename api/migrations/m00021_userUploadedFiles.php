<?php

use LogicLeap\PhpServerCore\MigrationScheme;

class m00021_userUploadedFiles extends MigrationScheme
{
    public static function isReversible(): bool
    {
        return true;
    }

    public static function up(): bool
    {
        $sql = "alter table users drop if exists documents ;
                create table if not exists user_files (
                    id int auto_increment primary key not null,
                    user_id int not null,
                    file_name varchar(100) not null,
                    received_file_name varchar(500) not null,
                    uploaded_date datetime not null,
                    deleted boolean not null
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
        $sql = "drop table if exists user_files";
        try {
            self::$pdo->exec($sql);
            return true;
        } catch (Exception) {
            return false;
        }
    }
}