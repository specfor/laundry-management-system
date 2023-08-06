<?php

namespace LogicLeap\StockManagement\models;

class API
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
}