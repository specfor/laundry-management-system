<?php

namespace LogicLeap\StockManagement\models;

use DateTime;
use PDO;

class Customers extends DbModel
{
    private const TABLE_NAME = 'customers';

    public static function addNewCustomer(string $name, string $email = null, string $phoneNumber = null,
                                          string $address = null, int $branchID = null): bool|array
    {
        $today = (new DateTime('now'))->format('Y-m-d');
        $params = [];
        $params['name'] = $name;
        $params['email'] = $email;
        $params['phone_num'] = $phoneNumber;
        $params['address'] = $address;
        $params['branch_id'] = $branchID;
        $params['joined_date'] = $today;
        $params['banned'] = false;

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return false;
        else
            return ['customer_id' => $id];
    }

    public static function getCustomers(int    $customerId = null, int $branchId = 0, string $email = null,
                                        string $phoneNumber = null, string $name = null, string $address = null, bool $banned = null,
                                        string $joinDate = null, int $pageNumber = 0, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($customerId)
            $filters[] = "customer_id=$customerId";
        if ($email) {
            $filters[] = "email LIKE :email";
            $placeholders['email'] = "%" . $email . "%";
        }
        if ($phoneNumber) {
            $filters[] = "phone_num LIKE :phone_num";
            $placeholders['phone_num'] = $phoneNumber . "%";
        }
        if ($name) {
            $filters[] = "name LIKE :name";
            $placeholders['name'] = "%" . $name . "%";
        }
        if ($address) {
            $filters[] = "address LIKE :address";
            $placeholders['address'] = "%" . $address . "%";
        }
        if ($banned) {
            $filters[] = "banned=$banned";
        }
        if ($joinDate) {
            $filters[] = "join_date LIKE :join_date";
            $placeholders['join_date'] = "%" . $joinDate . "%";
        }
        if ($branchId != 0)
            $filters[] = "branch_id=$branchId'";

        $condition = implode(' AND ', $filters);
        $statement = self::getDataFromTable(['customer_id', 'email', 'phone_num', 'name', 'address', 'branch_id', 'banned', 'joined_date'],
            self::TABLE_NAME, $condition, $placeholders, ['customer_id', 'desc'], [$startingIndex, $limit]);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as &$customer) {
            $customer['banned'] = boolval($customer['banned']);
        }
        return $data;
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

        return self::updateTableData(self::TABLE_NAME, $updateFieldsWithValues, "customer_id=$customerId");
    }

    public static function deleteCustomer(int $customerId): bool
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE customer_id=$customerId";
        if (self::exec($sql))
            return true;
        return false;
    }
}