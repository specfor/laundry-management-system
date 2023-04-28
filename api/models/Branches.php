<?php

namespace LogicLeap\StockManagement\models;

use PDO;

class Branches extends DbModel
{
    public static function addNewBranch(string $name, string $address = null, int $managerId = null,
                                        string $phoneNumber = null): bool
    {
        $sql = "INSERT INTO branches (name, address, manager_id, blocked, phone_num) 
                VALUES (?,?,$managerId, false, ?)";
        $statement = self::prepare($sql);
        return $statement->execute([$name, $address, $phoneNumber]);
    }

    public static function getBranches(int $pageNumber = 0, string $name = null, string $address = null,
                                       int $managerId = null, string $phoneNumber = null, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($name) {
            $filters[] = "name=:name";
            $placeholders['name'] = $name;
        }
        if ($address) {
            $filters[] = "address=:address";
            $placeholders['address'] = $address;
        }
        if ($managerId) {
            $filters[] = "manager_id=$managerId";
        }
        if ($phoneNumber) {
            $filters[] = "phone_num=:phone_num";
            $placeholders['phone_num'] = $phoneNumber;
        }
        $condition = null;
        if ($filters)
            $condition = implode(' AND ', $filters);

        $statement = self::getDataFromTable(['branch_id', 'name', 'address', 'manager_id', 'phone_num'],
            'branches', $condition, $placeholders, ['branch_id', 'asc'], [$startingIndex, $limit]);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateBranch(int $branchId, string $name = null, string $address = null,
                                        int $managerId = null, string $phoneNumber = null): bool
    {
        $updateFieldsWithValues = [];
        if ($name)
            $updateFieldsWithValues['name'] = $name;
        if ($address)
            $updateFieldsWithValues['address'] = $address;
        if ($managerId)
            $updateFieldsWithValues['manager_id'] = $managerId;
        if ($phoneNumber)
            $updateFieldsWithValues['phone_num'] = $phoneNumber;

        return self::updateTableData('branches', $updateFieldsWithValues, "branch_id=$branchId");
    }

    public static function deleteBranch(int $branch_id): bool
    {
        $sql = "DELETE FROM branches WHERE branch_id=$branch_id";
        if (self::exec($sql))
            return true;
        return false;
    }
}