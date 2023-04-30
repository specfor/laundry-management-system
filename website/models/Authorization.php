<?php

namespace LogicLeap\StockManagement\models;

use LogicLeap\StockManagement\core\Application;
use PDO;

class Authorization
{
    public static function getUserRole(string $authToken): int
    {
        $sql = "SELECT role FROM users where id=(select user_id from user_status where auth_token='$authToken')";
        $statement = Application::$app->db->pdo->prepare($sql);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC)['role'];
    }
}