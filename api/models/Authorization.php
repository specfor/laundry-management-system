<?php

namespace LogicLeap\StockManagement\models;

use DateInterval;
use DateTime;
use PDO;

class Authorization extends DbModel
{
    private const TOKEN_EXPIRE_INTERVAL = 43200;

    public static function isValidToken($token): bool
    {
        $sql = "SELECT exp_time FROM user_status WHERE auth_token=?";
        $statement = self::prepare($sql);
        $statement->bindValue(1, $token);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$data)
            return false;

        $time = new DateTime('now');
        $time = $time->format('Y-m-d H:i:s');

        if ($data['exp_time'] < $time)
            return false;
        return true;
    }

    public static function markSuccessfulLogin(int $userId, string $authToken, string $idAddress): void
    {
        $now = new DateTime('now');
        $time = $now->format('Y-m-d H:i:s');

        $sql = "SELECT id FROM user_status WHERE user_id=$userId";
        $statement = self::prepare($sql);
        $statement->execute();
        $expTime = $now->add(DateInterval::createFromDateString(self::TOKEN_EXPIRE_INTERVAL. ' seconds'));
        $expTime = $expTime->format('Y-m-d H:i:s');
        if ($statement->fetch(PDO::FETCH_ASSOC)) {
            $sql = "UPDATE user_status SET auth_token='$authToken', last_active='$time', ip_addr='$idAddress', exp_time='$expTime' WHERE user_id=$userId";
        } else {
            $sql = "INSERT INTO user_status (user_id, auth_token, last_active, ip_addr, exp_time) VALUES 
                                                        ($userId, '$authToken', '$time', '$idAddress', '$expTime')";
        }
        self::exec($sql);
    }

    public static function getUserId(string $token)
    {
        $sql = "SELECT user_id FROM user_status WHERE auth_token=?";
        $statement = self::prepare($sql);
        $statement->bindValue(1, $token);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        return $data['user_id'];
    }
}