<?php

namespace LogicLeap\StockManagement\core;

abstract class MigrationScheme
{
    public abstract static function isReversible(): bool;

    public abstract static function up(): bool;

    public abstract static function down(): bool;

    protected static \PDO $pdo;

    public function __construct()
    {
        self::$pdo = Application::$app->db->pdo;
    }
}