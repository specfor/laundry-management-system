<?php

namespace LogicLeap\StockManagement\models;

use PDO;

class Authorization extends DbModel
{
    public static function getUserRole(string $authToken): int
    {
        $sql = "SELECT role FROM users where id=(select user_id from user_status where auth_token='$authToken')";
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC)['role'];
    }
}