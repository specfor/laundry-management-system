<?php

namespace LogicLeap\StockManagement\controllers\v1\server_admin;

use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\PhpServerCore\ServerMetrics;
use LogicLeap\PhpServerCore\StorageManager;
use LogicLeap\StockManagement\controllers\v1\Controller;

class ServerStatusController extends Controller
{
    // Server Status Functions

    public function getRealtimePerformanceMetrics(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $ram = ServerMetrics::getMemoryUsage();
        $cpu = ServerMetrics::getCpuUsage();

        self::sendSuccess(['ram-usage' => $ram, 'cpu-load' => $cpu]);
    }

    public function getMaintenanceStatus(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        self::sendSuccess(['maintenance-mode' => (new MigrationManager())->isInMaintenanceMode()]);
    }

    public function setMaintenanceStatus(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $enable = self::getParameter('enable', dataType: 'bool', isCompulsory: true);

        (new MigrationManager())->addMaintenanceMode($enable);
        if ($enable)
            self::sendSuccess("Maintenance mode enabled.");
        else
            self::sendSuccess('Maintenance mode disabled.');
    }

    public function getAdminPanel(): void
    {
        self::checkPermissions(onlyServerAdmins: true);

        $page = self::getParameter('page');

        preg_match('/^[A-Za-z]+$/', $page, $pageName);

        if (empty($pageName[0]))
            exit();

        $pageName = 'admin_portal_html/' . $pageName[0] . '.html';
        StorageManager::streamFile($pageName, true);
    }
}