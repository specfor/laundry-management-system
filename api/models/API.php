<?php

namespace LogicLeap\StockManagement\models;

use Exception;
use LogicLeap\PhpServerCore\Application;
use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\StockManagement\models\user_management\Authorization;
use LogicLeap\StockManagement\models\user_management\User;

abstract class API
{
    public const STATUS_CODE_SUCCESS = 200;
    public const STATUS_CODE_NOTFOUND = 404;
    public const STATUS_CODE_FORBIDDEN = 403;
    public const STATUS_CODE_MAINTENANCE = 503;
    public const STATUS_CODE_UNAUTHORIZED = 401;
    public const STATUS_CODE_SERVER_ERROR = 500;

    public const STATUS_MSG_SUCCESS = 'success';
    public const STATUS_MSG_ERROR = 'error';
    public const STATUS_MSG_NOTFOUND = 'not-found';
    public const STATUS_MSG_FORBIDDEN = 'forbidden';
    public const STATUS_MSG_MAINTENANCE = 'maintenance';
    public const STATUS_MSG_UNAUTHORIZED = 'unauthorized';
    public const STATUS_MSG_SERVER_ERROR = 'server-error';

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
            if (!self::isSiteMigrator()) {
                self::sendResponse(API::STATUS_CODE_MAINTENANCE, API::STATUS_MSG_MAINTENANCE,
                    ['error' => 'Server is under maintenance']);
                exit();
            }
        }

        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
    }

    private static function isSiteMigrator(): bool
    {
        if (isset($_SERVER['HTTP_X_ADMINISTRATOR_TOKEN'])) {
            if ((new MigrationManager())->validateMigrationAuthToken($_SERVER['HTTP_X_ADMINISTRATOR_TOKEN']))
                return true;
        }
        return false;
    }

    /**
     * Send JSON formatted crafted response to the called API user.
     * @param int $statusCode Response status code. Can ues one of the constants defined in API.
     * @param string $statusMessage Response status message. Can ues one of the constants defined in API.
     * @param array $payload Payload to encode. Should be a nested array of key=>value pairs.
     */
    public static function sendResponse(int $statusCode, string $statusMessage, array $payload): void
    {
        header("Content-Type: application/json");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $finalPayload = [
            'statusCode' => $statusCode,
            'statusMessage' => $statusMessage,
            'body' => $payload
        ];
        echo json_encode($finalPayload);
    }

    public static function endPointNotFound(): void
    {
        $finalPayload = [
            'statusCode' => self::STATUS_CODE_NOTFOUND,
            'statusMessage' => self::STATUS_MSG_NOTFOUND,
            'body' => ['error' => 'Requested API endpoint was not found.']
        ];
        echo json_encode($finalPayload);
    }

    /**
     * Check whether requests are coming from authorized users. If not send "401" unauthorized error message.
     * @param array $permissions Array of permissions needed. [roleGroup => [permissions], ...]
     */
    public static function checkPermissions(array $permissions = [], bool $onlyServerAdmins = false): void
    {
        preg_match('/Bearer\s(\S+)/', self::getAuthorizationHeader(), $matches);

        if (!$matches || !Authorization::isValidToken($matches[1])) {
            self::sendResponse(self::STATUS_CODE_FORBIDDEN, self::STATUS_MSG_FORBIDDEN,
                ['message' => 'You are not authorized to perform this action.']);
            exit();
        }

        $permitted = true;

        $userRole = User::getUserRole(self::getUserId());

        if (!$permissions && !$onlyServerAdmins)
            return;

        if ($userRole == User::ROLE_SUPER_ADMINISTRATOR)
            return;

        if (!$onlyServerAdmins) {
            $permissionGroups = array_keys(User::PERMISSIONS);
            foreach ($permissions as $group => $permissions_) {
                if (!in_array($group, $permissionGroups)) {
                    $permitted = false;
                    break;
                }
                foreach ($permissions_ as $item) {
                    if (!in_array($item, User::PERMISSIONS[$group])) {
                        $permitted = false;
                        break;
                    }
                }
            }
        } else
            $permitted = false;

        if (!$permitted) {
            self::sendResponse(self::STATUS_CODE_FORBIDDEN, self::STATUS_MSG_FORBIDDEN,
                ['message' => 'You are not authorized to perform this action.']);
        }
        exit();
    }

    public static function getUserId(): int
    {
        preg_match('/Bearer\s(\S+)/', self::getAuthorizationHeader(), $matches);
        return Authorization::getUserId($matches[1]);
    }

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
     * Retrieve parameters passed either by GET or POST methods.
     * @param string $parameterIdentifier Parameter name
     * @param mixed $defaultValue Default value to set if parameter is not passed.
     * @param string $dataType Datatype if needed to get converted to a specific data type. Send an error message to
     *                          the user if passed data cannot be converted to the specified type.
     * @param bool $isCompulsory If set to True, send an error message if specified parameter is not passed.
     * @return mixed Value of the parameter.
     */
    public static function getParameter(string $parameterIdentifier, mixed $defaultValue = null,
                                         string $dataType = 'string', bool $isCompulsory = false): mixed
    {
        $params = Application::$app->request->getBodyParams();

        if (!isset($params[$parameterIdentifier]))
            if ($isCompulsory)
                self::sendError("Required parameter '$parameterIdentifier' is missing.");
            else
                return $defaultValue;

        return self::getConvertedTo($parameterIdentifier, $params[$parameterIdentifier], $dataType);
    }

    /**
     * Convert to a specific data type. If value cannot be converted, send an error to user.
     * @param string $parameterName request parameter to send with error message
     * @param mixed $value value to be converted
     * @param string $dataType 'int', 'float', 'bool'. Should be one of those
     * @return mixed Converted data
     */
    public static function getConvertedTo(string $parameterName, mixed $value, string $dataType): mixed
    {
        try {
            if ($dataType == 'string') {
                if (!is_string($value))
                    throw new Exception('string required.');
            } elseif ($dataType == 'int')
                $value = intval($value);
            elseif ($dataType == 'float')
                $value = floatval($value);
            elseif ($dataType == 'bool')
                $value = boolval($value);
            elseif ($dataType == 'decimal') {
                if (!preg_match('/^[+-]?([0-9]+([.][0-9]*)?|[.][0-9]+)$/', $value))
                    throw new Exception('invalid decimal number');
                if ("$value"[0] == '.')
                    $value = "0$value";
            } elseif ($dataType == 'array')
                if (!is_array($value))
                    throw new Exception('array required.');
            return $value;
        } catch (Exception) {
            self::sendError("$parameterName must be type '$dataType'");
            return null;
        }
    }

    /**
     * Send an error message to requested user and exit program execution.
     * @param string $message Error message to send.
     */
    public static function sendError(string $message): void
    {
        self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_ERROR,
            ['message' => $message]);
        exit();
    }

    public static function sendSuccess(array|string $body): void
    {
        if (is_array($body))
            self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_SUCCESS, $body);
        else
            self::sendResponse(self::STATUS_CODE_SUCCESS, self::STATUS_MSG_SUCCESS,
                ['message' => $body]);
        exit();
    }

    public function optionsRequest()
    {
        // Access-Control headers are received during OPTIONS requests
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    public function errorHandler(int|string $errorCode, string $errorMessage): void
    {
        if ($errorCode === 404)
            self::sendResponse(self::STATUS_CODE_NOTFOUND, self::STATUS_MSG_NOTFOUND,
                ['message' => $errorMessage]);
        elseif ($errorCode === 403)
            self::sendResponse(self::STATUS_CODE_FORBIDDEN, self::STATUS_MSG_FORBIDDEN,
                ['message' => $errorMessage]);
        else
            self::sendResponse(self::STATUS_CODE_SERVER_ERROR, self::STATUS_MSG_SERVER_ERROR,
                ['message' => 'A server error occurred.']);
        exit();
    }
}