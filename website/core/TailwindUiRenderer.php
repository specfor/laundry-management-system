<?php

namespace LogicLeap\StockManagement\core;

class TailwindUiRenderer
{
    public static function loadComponent(string $componentName): void
    {
        require_once Application::$ROOT_DIR. "/views/components/$componentName.php";
    }

    public static function loadPage(string $pageName, array $variableData = null):void
    {
        require_once Application::$ROOT_DIR. "/views/$pageName.php";
    }
}