<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\StockManagement\core\TailwindUiRenderer;
use LogicLeap\StockManagement\models\Authorization;
use LogicLeap\StockManagement\models\DbModel;

class SiteController
{
    private const SITE_NAME = 'Laundry System';

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

    private function checkPermission(int $minimumPermittedUserRole)
    {
        $userRole = Authorization::getUserRole($_COOKIE['auth-token']);

        if ($userRole > $minimumPermittedUserRole) {
            echo "You are not allowed";
            exit();
        }
    }

    public function errorHandler(int $errorCode, string $errorMessage): void
    {
        if ($errorCode === 404)
            TailwindUiRenderer::loadPage('_404');
        elseif ($errorCode === 403)
            echo 'you are not allowed';
        else
            echo 'server error';
        exit();
    }
}