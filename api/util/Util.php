<?php

namespace LogicLeap\StockManagement\Util;

use LogicLeap\StockManagement\models\API;
use Exception;
use LogicLeap\PhpServerCore\Application;
use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\StockManagement\models\user_management\Authorization;
use LogicLeap\StockManagement\models\user_management\User;

class Util
{
    public static function getAuthorizationHeader(): string|null
    {
        $authHeader = null;
        if (isset($_SERVER['Authorization'])) {
            $authHeader = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $authHeader = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $authHeader = trim($requestHeaders['Authorization']);
            }
        }
        return $authHeader;
    }

    /**
     * Convert to a specific data type. If value cannot be converted, send an error to user.
     * @param mixed $value value to be converted
     * @param string $dataType 'int', 'float', 'bool'. Should be one of those
     * @return mixed Converted data
     */
    public static function getConvertedTo(mixed $value, string $dataType): mixed
    {
        try {
            if ($dataType == 'string') {
                if (!is_string($value))
                    throw new Exception('string required.');
            } elseif ($dataType == 'int') {
                if (!preg_match('/^[+-]?([0-9]+)$/', $value))
                    throw new Exception('invalid integer number');
                $value = intval($value);
            } elseif ($dataType == 'float') {
                if (!preg_match('/^[+-]?([0-9]+([.][0-9]*)?|[.][0-9]+)$/', $value))
                    throw new Exception('invalid float number');
                $value = floatval($value);
            } elseif ($dataType == 'bool') {
                if (!is_bool($value)) {
                    if (!preg_match('/(^false$|^true$|^0$|^1$)/', $value))
                        throw new Exception('invalid decimal number');
                    $value = boolval($value);
                }
            } elseif ($dataType == 'decimal') {
                if (!preg_match('/^[+-]?([0-9]+([.][0-9]*)?|[.][0-9]+)$/', $value))
                    throw new Exception('invalid decimal number');
                if ("$value"[0] == '.')
                    $value = "0$value";
            } elseif ($dataType == 'array') {
                if (!is_array($value))
                    throw new Exception('array required.');
            }
            return $value;
        } catch (Exception) {
            return null;
        }
    }
}