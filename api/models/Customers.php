<?php

namespace LogicLeap\StockManagement\models;

use PDO;

class Customers extends DbModel
{
    private const TABLE_NAME = 'customers';

    public string $email;
    public string $firstname;
    public string $lastname;
    public string $phoneNumber;
    public string $address;

    public static function addNewCustomer(string $name, string $email,
                                          string $phoneNumber, string $address, int $branchID): bool
    {
        if ($branchID == 0)
            $branchID = null;

        $sql = "INSERT INTO " . self::TABLE_NAME . " (email, name, phone_num, address, branch_id) VALUES 
                (':email', ':name', ':phone_num', ':address', $branchID)";
        $statement = self::prepare($sql);
        $statement->bindValue(':email', $email);
        $statement->bindValue(':name', $name);
        $statement->bindValue(':phone_num', $phoneNumber);
        $statement->bindValue(':address', $address);
        return $statement->execute();
    }

    public static function getCustomers(int $branchId = 0, int $startingIndex = 0, int $limit = 30): array
    {
        if ($branchId == 0)
            $sql = "SELECT * FROM " . self::TABLE_NAME . " ORDER BY customer_id LIMIT $startingIndex, $limit";
        else
            $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE branch_id=$branchId ORDER BY customer_id LIMIT $startingIndex, $limit";
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}