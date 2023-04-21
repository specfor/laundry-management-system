<?php

namespace LogicLeap\StockManagement\core;

class SecureToken
{
    public static function generateToken(): string
    {
        $uniqueText = bin2hex(random_bytes(20)).'J7G_E#9sgRaj09';
        return hash('sha256', $uniqueText);
    }

}