<?php

namespace LogicLeap\StockManagement\controllers\v1;

use LogicLeap\PhpServerCore\Application;
use LogicLeap\PhpServerCore\MigrationManager;
use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\user_management\Authorization;
use LogicLeap\StockManagement\models\user_management\User;
use LogicLeap\StockManagement\Util\Util;

class Controller
{
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
                self::sendResponse(
                    API::STATUS_CODE_MAINTENANCE,
                    API::STATUS_MSG_MAINTENANCE,
                    ['error' => 'Server is under maintenance']
                );
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

    /**
     * Check whether requests are coming from authorized users. If not send "401" unauthorized error message.
     * @param array $permissions Array of permissions needed. [roleGroup => [permissions], ...]
     */
    public static function checkPermissions(array $permissions = [], bool $onlyServerAdmins = false): void
    {
        preg_match('/Bearer\s(\S+)/', Util::getAuthorizationHeader(), $matches);

        if (!$matches || !Authorization::isValidToken($matches[1])) {
            self::sendResponse(
                API::STATUS_CODE_FORBIDDEN,
                API::STATUS_MSG_FORBIDDEN,
                ['message' => 'You are not authorized to perform this action.']
            );
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
            self::sendResponse(
                API::STATUS_CODE_FORBIDDEN,
                API::STATUS_MSG_FORBIDDEN,
                ['message' => 'You are not authorized to perform this action.']
            );
        }
        exit();
    }

    public static function getUserId(): int
    {
        preg_match('/Bearer\s(\S+)/', Util::getAuthorizationHeader(), $matches);
        return Authorization::getUserId($matches[1]);
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
    public static function getParameter(
        string $parameterIdentifier,
        mixed $defaultValue = null,
        string $dataType = 'string',
        bool $isCompulsory = false
    ): mixed {
        $params = Application::$app->request->getBodyParams();

        if (!isset($params[$parameterIdentifier]))
            if ($isCompulsory)
                self::sendError("Required parameter '$parameterIdentifier' is missing.");
            else
                return $defaultValue;

        $ret = Util::getConvertedTo($parameterIdentifier, $params[$parameterIdentifier], $dataType);
        if ($ret == null) {
            self::sendError("$parameterIdentifier must be type '$dataType'");
        } else return $ret;

        // return self::getConvertedTo($parameterIdentifier, $params[$parameterIdentifier], $dataType);
    }

    /**
     * Send an error message to requested user and exit program execution.
     * @param string $message Error message to send.
     */
    public static function sendError(string $message): void
    {
        self::sendResponse(
            API::STATUS_CODE_SUCCESS,
            API::STATUS_MSG_ERROR,
            ['message' => $message]
        );
        exit();
    }

    public static function sendSuccess(array|string $body): void
    {
        if (is_array($body))
            self::sendResponse(API::STATUS_CODE_SUCCESS, API::STATUS_MSG_SUCCESS, $body);
        else
            self::sendResponse(
                API::STATUS_CODE_SUCCESS,
                API::STATUS_MSG_SUCCESS,
                ['message' => $body]
            );
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
            self::sendResponse(
                API::STATUS_CODE_NOTFOUND,
                API::STATUS_MSG_NOTFOUND,
                ['message' => $errorMessage]
            );
        elseif ($errorCode === 403)
            self::sendResponse(
                API::STATUS_CODE_FORBIDDEN,
                API::STATUS_MSG_FORBIDDEN,
                ['message' => $errorMessage]
            );
        else
            self::sendResponse(
                API::STATUS_CODE_SERVER_ERROR,
                API::STATUS_MSG_SERVER_ERROR,
                ['message' => 'A server error occurred.']
            );
        exit();
    }
}
