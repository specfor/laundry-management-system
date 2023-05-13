<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\PhpServerCore\Application;
use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\PhpServerCore\TailwindUiRenderer;
use LogicLeap\StockManagement\models\Authorization;

class SiteController
{
    private const SITE_NAME = 'Laundry System';
    private int $userRole;

    // User Roles
    private const ROLE_SUPER_ADMINISTRATOR = 0;
    private const ROLE_ADMINISTRATOR = 1;
    private const ROLE_MANAGER = 2;
    private const ROLE_CASHIER = 3;

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
        $this->checkPermission(self::ROLE_CASHIER);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'Dashboard - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('dashboard', $variableData);
    }

    public function getBranches(): void
    {
        $this->checkPermission(self::ROLE_ADMINISTRATOR);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'Branches - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('branches', $variableData);
    }

    public function getEmployees(): void
    {
        $this->checkPermission(self::ROLE_ADMINISTRATOR);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'Employees - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('employees', $variableData);
    }

    public function getPayments(): void
    {
        $this->checkPermission(self::ROLE_ADMINISTRATOR);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'Payments - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('payments', $variableData);
    }

    public function getUsers(): void
    {
        $this->checkPermission(self::ROLE_ADMINISTRATOR);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'Users - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('users', $variableData);
    }

    public function getProducts(): void
    {
        $this->checkPermission(self::ROLE_ADMINISTRATOR);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'Products - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('products', $variableData);
    }

    public function getCustomers(): void
    {
        $this->checkPermission(self::ROLE_ADMINISTRATOR);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'Customers - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('customers', $variableData);
    }

    public function newOrder(): void
    {
        $this->checkPermission(self::ROLE_CASHIER);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'New Order - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('addOrder', $variableData);
    }

    public function finalizeOrder(): void
    {
        $this->checkPermission(self::ROLE_CASHIER);

        $variableData['header'] = self::getHeaderMenuItems($this->userRole);
        $variableData['site-title'] = 'Finalize Order - ' . self::SITE_NAME;
        TailwindUiRenderer::loadPage('finalPayment', $variableData);
    }

    private static function getHeaderMenuItems(int $userRole = self::ROLE_CASHIER): array
    {
        $header = [
            ['label' => 'Dashboard', 'path' => '/dashboard', 'onclick' => 'window.location.href="/dashboard"']
        ];
        if ($userRole < self::ROLE_ADMINISTRATOR) {
            $header[] = ['label' => 'Employees', 'onclick' => "window.location.href='/employees'"];
            $header[] = ['label' => 'Branches', 'onclick' => "window.location.href='/branches'"];
            $header[] = ['label' => 'Products', 'onclick' => "window.location.href='/products'"];
            $header[] = ['label' => 'Users', 'onclick' => "window.location.href='/users'"];
        }
        $header[] = ['label' => 'Log Out', 'path' => '#',
            'onclick' => "document.cookie='auth-token=; expires=Thu, 01 Jan 1970 00:00:00 UTC;';window.location.href='/'"];
        return $header;
    }

    // Server Status Functions

    public function getRealtimePerformanceMetrics(): void
    {
        $this->checkPermission(self::ROLE_SUPER_ADMINISTRATOR);

        TailwindUiRenderer::loadPage('serverPerformance');
    }

    public function getServerManager(): void
    {
        $this->checkPermission(self::ROLE_SUPER_ADMINISTRATOR);

        TailwindUiRenderer::loadPage('serverManager');
    }


    private function checkPermission(int $minimumPermittedUserRole): void
    {
        if (!isset($_COOKIE['auth-token']))
            $this->errorHandler(403, 'You are not allowed.');

        $this->userRole = Authorization::getUserRole($_COOKIE['auth-token']);

        if ($this->userRole > $minimumPermittedUserRole) {
            if ($minimumPermittedUserRole == self::ROLE_SUPER_ADMINISTRATOR) {
                $this->errorHandler(404, 'Page Not Found.');
            } else {
                $this->errorHandler(403, 'You are not allowed.');
            }
        }
    }

    public function errorHandler(int|string $errorCode, string $errorMessage = null): void
    {
        if ($errorCode === 404)
            TailwindUiRenderer::loadPage('_404');
        elseif ($errorCode === 403)
            header("Location: /");
        elseif ($errorCode === 503)
            TailwindUiRenderer::loadPage('_503');
        else
            TailwindUiRenderer::loadPage('_500');
        exit();
    }
}