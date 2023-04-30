<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\StockManagement\core\Application;
use LogicLeap\StockManagement\core\MigrationManager;
use LogicLeap\StockManagement\core\TailwindUiRenderer;
use LogicLeap\StockManagement\models\Authorization;
use LogicLeap\StockManagement\models\DbModel;

class SiteController
{
    private const SITE_NAME = 'Laundry System';

    public function __construct()
    {
        $maintenanceModeFilePath = Application::$ROOT_DIR . "/maintenanceLock.lock";
        $migrationModeFilePath = Application::$ROOT_DIR . "/migrationLock.lock";
        if (is_file($migrationModeFilePath)) {
            $migrationManager = new MigrationManager();
            $migrationManager->startMigration();
        }

        // If in maintenance mode, maintenance page is displayed. Application exits.
        if (is_file($maintenanceModeFilePath)) {
            $this->errorHandler(503);
        }
    }

    public function login(): void
    {
        $variableData['site-title'] = 'Login - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('login', $variableData);
    }

    public function dashboard(): void
    {
        $variableData['site-title'] = 'Dashboard - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('dashboard', $variableData);
    }

    public function getBranches(): void
    {
        $variableData['site-title'] = 'Branches - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('branches', $variableData);
    }

    public function getEmployees(): void
    {
        $variableData['site-title'] = 'Employees - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('employees', $variableData);
    }

    public function getPayments(): void
    {
        $variableData['site-title'] = 'Payments - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('payments', $variableData);
    }

    public function getUsers(): void
    {
        $variableData['site-title'] = 'Users - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('users', $variableData);
    }

    public function getProducts(): void
    {
        $variableData['site-title'] = 'Products - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('products', $variableData);
    }

    private function checkPermission(int $minimumPermittedUserRole)
    {
        $userRole = Authorization::getUserRole($_COOKIE['auth-token']);

        if ($userRole > $minimumPermittedUserRole) {
            $this->errorHandler(403, 'You are not allowed.');
        }
    }

    public function errorHandler(int|string $errorCode, string $errorMessage = null): void
    {
        if ($errorCode === 404)
            TailwindUiRenderer::loadPage('_404');
        elseif ($errorCode === 403)
            TailwindUiRenderer::loadPage('_403');
        elseif ($errorCode === 503)
            TailwindUiRenderer::loadPage('_503');
        else
            TailwindUiRenderer::loadPage('_500');
        exit();
    }
}