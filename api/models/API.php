<?php

namespace LogicLeap\StockManagement\models;

abstract class API
{
    public const STATUS_CODE_SUCCESS = 200;
    public const STATUS_CODE_NOTFOUND = 404;
    public const STATUS_CODE_FORBIDDEN = 403;
    public const STATUS_CODE_MAINTENANCE = 503;
    public const STATUS_CODE_UNAUTHORIZED = 401;

    public const STATUS_MSG_SUCCESS = 'success';
    public const STATUS_MSG_ERROR = 'error';
    public const STATUS_MSG_NOTFOUND = 'not-found';
    public const STATUS_MSG_FORBIDDEN = 'forbidden';
    public const STATUS_MSG_MAINTENANCE = 'maintenance';
    public const STATUS_MSG_UNAUTHORIZED = 'unauthorized';

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
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['dev'])){
            $finalPayload['dev'] = $data;
        }
        echo json_encode($finalPayload);
    }

    public static function endPointNotFound(): void
    {
        $finalPayload = [
            'statusCode' => self::STATUS_CODE_NOTFOUND,
            'statusMessage' => self::STATUS_MSG_NOTFOUND,
            'body' => ['error' => 'API endpoint not found']
        ];
        echo json_encode($finalPayload);
    }
}