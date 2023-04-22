<?php

namespace LogicLeap\StockManagement\models;

use mysql_xdevapi\Statement;
use PDO;

class Branches extends DbModel
{
    public static function addNewBranch(string $name, string $address, int $managerId): bool
    {
        $sql = "INSERT INTO branches (name, address, manager_id, blocked) VALUES (?,?,$managerId, false)";
        $statement = self::prepare($sql);
        return $statement->execute([$name, $address]);
    }

    public static function getBranches(): array
    {
        $sql = "SELECT * FROM branches";
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateBranch(int $branchId, string $name = null, string $address = null,
                                        int $managerId = null): bool
    {
        $updateFieldsWithValues = [];
        if ($name)
            $updateFieldsWithValues['name'] = $name;
        if ($address)
            $updateFieldsWithValues['address'] = $address;
        if ($managerId)
            $updateFieldsWithValues['manager_id'] = $managerId;

        return self::updateTableData('branches', $updateFieldsWithValues, "branch_id=$branchId");
    }
}