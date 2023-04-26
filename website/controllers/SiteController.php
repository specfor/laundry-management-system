<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\StockManagement\core\TailwindUiRenderer;

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
}