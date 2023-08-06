<?php

namespace LogicLeap\StockManagement\controllers\v1\server_admin;

use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\PhpServerCore\Controller;

class MigrationsController extends Controller
{   
    public function getMigrations(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $migrations = (new MigrationManager())->getAvailableMigrations(true);
        self::sendSuccess(['available-migrations' => $migrations]);
    }

    public function getAppliedMigrations(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $migrationName = self::getParameter('migration-name');
        $appliedTime = self::getParameter('applied-time');
        $status = self::getParameter('status', dataType: 'bool');

        self::sendSuccess(['applied-migrations' => (new MigrationManager())
            ->getCompletedMigrations($pageNumber, $migrationName, $appliedTime, $status)]);
    }

    public function attemptMigration(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $migrationName = self::getParameter('migration-name', isCompulsory: true);
        $force = self::getParameter('force-run', defaultValue: false, dataType: 'bool');

        $manager = new MigrationManager();
        $status = $manager->startMigration($migrationName, $force);
        if ($status === true)
            self::sendSuccess("Successfully ran the migration.");
        elseif ($status === false)
            self::sendError("Failed to run the migration.");
        else
            self::sendError($status);
    }

    public function getMigrationToken(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        self::sendSuccess(['token' => (new MigrationManager())->getMigrationAuthToken()]);
    }

    public function blockMigrationToken(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $token = self::getParameter('token', isCompulsory: true);

        $status = (new MigrationManager())->blockMigrationAuthToken($token);
        if ($status === true)
            self::sendSuccess('Successfully blocked the token.');
        elseif ($status === false)
            self::sendError('Failed to block the token');
        else
            self::sendError($status);
    }

    public function validateMigrationToken(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $token = self::getParameter('token', isCompulsory: true);

        if ((new MigrationManager())->validateMigrationAuthToken($token))
            self::sendSuccess('Token is valid.');
        else {
            self::sendError('Token is expired.');
        }
    }
}