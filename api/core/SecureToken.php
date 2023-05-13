<?php

namespace LogicLeap\PhpServerCore;

class SecureToken
{
    public static function generateToken(): string
    {
        $uniqueText = bin2hex(random_bytes(32));
        return hash('sha256', $uniqueText);
    }

}