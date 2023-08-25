<?php

namespace LogicLeap\StockManagement\controllers\v1\server_admin;

use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\PhpServerCore\Request;
use LogicLeap\PhpServerCore\Router;
use LogicLeap\PhpServerCore\SecureToken;
use LogicLeap\PhpServerCore\ServerMetrics;
use LogicLeap\PhpServerCore\FileHandler;
use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\user_management\Authorization;
use LogicLeap\StockManagement\models\user_management\User;

class ServerAdminPanelController extends Controller
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
        $page = self::getParameter('page');

        if (!$page) {
            $pageName = 'admin_portal_html/adminLogin.html';
        } else {
            $authToken = $_COOKIE['auth_token'] ?? null;

            if (empty($authToken) || !Authorization::isValidToken($authToken) ||
                User::getUserRole(Authorization::getUserId($authToken)) !== User::ROLE_SUPER_ADMINISTRATOR){
                self::sendResponse(
                    API::STATUS_CODE_FORBIDDEN,
                    API::STATUS_MSG_FORBIDDEN,
                    ['message' => 'You are not authorized to perform this action.']
                );
                exit();
            }

            preg_match('/^[A-Za-z0-9_-]+$/', $page, $pageName);

            if (empty($pageName[0]))
                exit();

            $pageName = 'admin_portal_html/' . $pageName[0] . '.html';
        }
        FileHandler::streamFile($pageName, true);
    }

    public function adminLogin(): void
    {
        $username = self::getParameter('username', isCompulsory: true);
        $password = self::getParameter('password', isCompulsory: true);
        $user = new User();

        if ($user->validateUser($username, $password)) {
            if (User::getUserRole($user->userId) !== User::ROLE_SUPER_ADMINISTRATOR)
                self::sendError('Incorrect Username or Password.');

            $token = SecureToken::generateToken();
            Authorization::markSuccessfulLogin($user->userId, $token, Request::getRequestIp());

            setcookie('auth_token', $token);
            self::sendSuccess(['url' => '/api/v1/server-manager/dashboard?page=serverManager', 'auth_token' => $token]);
        } else {
            self::sendError('Incorrect Username or Password.');
        }
    }
}