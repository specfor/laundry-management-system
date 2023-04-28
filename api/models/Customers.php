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

    public static function getCustomers(int    $branchId = 0, string $email = null, string $phoneNumber = null,
                                        string $name = null, string $address = null, bool $banned = null,
                                        string $joinDate = null, int $pageNumber = 0, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($email) {
            $filters[] = "email=:email";
            $placeholders['email'] = "%" . $email . "%";
        }
        if ($phoneNumber) {
            $filters[] = "phone_num=:phone_num";
            $placeholders['phone_num'] = "%" . $phoneNumber . "%";
        }
        if ($name) {
            $filters[] = "name=:name";
            $placeholders['name'] = "%" . $name . "%";
        }
        if ($address) {
            $filters[] = "address=:address";
            $placeholders['address'] = "%" . $address . "%";
        }
        if ($banned) {
            $filters[] = "banned=$banned";
        }
        if ($joinDate) {
            $filters[] = "join_date=:join_date";
            $placeholders['join_date'] = "%" . $joinDate . "%";
        }
        if ($branchId != 0)
            $filters[] = "branch_id=$branchId'";

        $condition = implode(' AND ', $filters);
        $statement = self::getDataFromTable(['email', 'phone_num', 'name', 'address', 'branch_id', 'banned', 'joined_date'],
            'customers', $condition, $placeholders, ['customer_id', 'desc'], [$startingIndex, $limit]);
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