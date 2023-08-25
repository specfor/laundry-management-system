<?php

namespace LogicLeap\StockManagement\models\user_management;

use DateInterval;
use DateTime;
use LogicLeap\StockManagement\models\DbModel;
use PDO;

class Authorization extends DbModel
{
    private const TOKEN_EXPIRE_INTERVAL = 43200;

    public static function isValidToken($token): bool
    {
        $sql = "SELECT exp_time FROM user_status WHERE auth_token=? order by id desc";
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
        $expTime = $now->add(DateInterval::createFromDateString(self::TOKEN_EXPIRE_INTERVAL . ' seconds'));
        $expTime = $expTime->format('Y-m-d H:i:s');

        $params['user_id'] = $userId;
        $params['auth_token'] = $authToken;
        $params['last_active'] = $time;
        $params['ip_addr'] = $idAddress;
        $params['exp_time'] = $expTime;

        self::insertIntoTable('user_status', $params);
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