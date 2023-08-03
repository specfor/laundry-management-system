<?php

namespace LogicLeap\StockManagement\models\stock_management;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class Branches extends DbModel
{
    public static function addNewBranch(string $name, string $address = null, int $managerId = null,
                                        string $phoneNumber = null): bool|array
    {
        $params['name'] = $name;
        if ($address)
            $params['address'] = $address;
        if ($managerId)
            $params['manager_id'] = $managerId;
        if ($phoneNumber)
            $params['phone_num'] = $phoneNumber;
        $params['blocked'] = false;

        $id = self::insertIntoTable('branches', $params);
        if ($id === false)
            return false;
        else
            return ['branch_id' => $id];
    }

    public static function getBranches(int $pageNumber = 0, int $branchId =null ,string $name = null, string $address = null,
                                       int $managerId = null, string $phoneNumber = null, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($branchId)
            $filters[] = "branch_id=$branchId";
        if ($name) {
            $filters[] = "name LIKE :name";
            $placeholders['name'] = "%".$name."%";
        }
        if ($address) {
            $filters[] = "address LIKE :address";
            $placeholders['address'] = "%".$address."%";
        }
        if ($managerId) {
            $filters[] = "manager_id=$managerId";
        }
        if ($phoneNumber) {
            $filters[] = "phone_num LIKE :phone_num";
            $placeholders['phone_num'] = $phoneNumber."%";
        }
        $condition = null;
        if ($filters)
            $condition = implode(' AND ', $filters);

        $statement = self::getDataFromTable(['branch_id', 'name', 'address', 'manager_id', 'phone_num'],
            'branches', $condition, $placeholders, ['branch_id', 'asc'], [$startingIndex, $limit]);
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