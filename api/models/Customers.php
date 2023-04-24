<?php

namespace LogicLeap\StockManagement\models;

use DateTime;
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
        $today = (new DateTime('now'))->format('Y-m-d');
        $sql = "INSERT INTO " . self::TABLE_NAME . " (email, name, phone_num, address, branch_id, joined_date, banned) VALUES 
                (?, ?, ?, ?, $branchID, '$today', false)";
        $statement = self::prepare($sql);
        $statement->bindValue(1, $email);
        $statement->bindValue(2, $name);
        $statement->bindValue(3, $phoneNumber);
        $statement->bindValue(4, $address);
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

    public static function updateCustomer(int    $customerId, string $name = null, string $email = null,
                                          string $phoneNumber = null, string $address = null,
                                          int    $branchID = null, bool $banned = null): bool
    {
        $updateFieldsWithValues = [];
        if ($name) {
            $updateFieldsWithValues['name'] = $name;
        }
        if ($email) {
            $updateFieldsWithValues['email'] = $email;
        }
        if ($phoneNumber) {
            $updateFieldsWithValues['phone_num'] = $phoneNumber;
        }
        if ($address) {
            $updateFieldsWithValues['address'] = $address;
        }
        if ($branchID) {
            $updateFieldsWithValues['branch_id'] = $branchID;
        }
        if ($banned) {
            $updateFieldsWithValues['banned'] = $banned;
        }

        return self::updateTableData('customers', $updateFieldsWithValues, "customer_id=$customerId");
    }

    public static function deleteCustomer(int $customerId): bool
    {
        $sql = "DELETE FROM customers WHERE customer_id=$customerId";
        if (self::exec($sql))
            return true;
        return false;
    }
}