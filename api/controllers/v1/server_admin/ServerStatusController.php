<?php

namespace LogicLeap\StockManagement\controllers\v1\server_admin;

use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\PhpServerCore\ServerMetrics;
use LogicLeap\StockManagement\models\API;

class ServerStatusController extends API
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
}