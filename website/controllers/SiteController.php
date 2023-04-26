<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\StockManagement\core\Application;

class SiteController
{
    public function login():void
    {

    }

    public function dashboard():void
    {

    }

    public function getBranches():void
    {
        require_once Application::$ROOT_DIR."/views/branches.html";
    }

    public function getEmployees():void
    {
        require_once Application::$ROOT_DIR."/views/employees.html";
    }

    public function getPayments():void
    {
        require_once Application::$ROOT_DIR."/views/payments.html";
    }

    public function getUsers():void
    {
        require_once Application::$ROOT_DIR."/views/users.html";
    }
}