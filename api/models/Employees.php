<?php

namespace LogicLeap\StockManagement\models;

use PDO;

class Employees extends DbModel
{
    public static function addEmployee(string $name, string $address = null, string $email = null,
                                       string $phoneNumber = null, int    $branchId = null, string $joinDate = null,
                                       string $left_date = null): bool
    {
        $isLeft = false;
        if ($left_date) {
            $isLeft = true;
        }

        $sql = "INSERT INTO employees (name, address, email, phone_num, branch_id, join_date, left_date, is_left) 
                        VALUES (:name, :address, :email, :phone_num, $branchId, :join_date, :left_date, $isLeft)";
        $statement = self::prepare($sql);
        $statement->bindValue(':name', $name);
        $statement->bindValue(':address', $address);
        $statement->bindValue(':email', $email);
        $statement->bindValue(':phone_num', $phoneNumber);
        $statement->bindValue(':join_date', $joinDate);
        return $statement->execute();
    }

    public static function updateEmployee(int    $employeeId, string $name = null, string $address = null, string $email = null,
                                          string $phoneNumber = null, int $branchId = null, string $joinDate = null,
                                          string $left_date = null): bool
    {
        $updateFieldsWithValues = [];
        if ($name)
            $updateFieldsWithValues['name'] = $name;
        if ($address)
            $updateFieldsWithValues['address'] = $address;
        if ($email)
            $updateFieldsWithValues['email'] = $email;
        if ($phoneNumber)
            $updateFieldsWithValues['phone_num'] = $phoneNumber;
        if ($branchId)
            $updateFieldsWithValues['branch_id'] = $branchId;
        if ($left_date) {
            $updateFieldsWithValues['left_date'] = $left_date;
            $updateFieldsWithValues['is_left'] = true;
        }
        return self::updateTableData('employees', $updateFieldsWithValues, "employee_id=$employeeId");
    }

    public static function getEmployees(int    $pageNumber = 0, string $name = null, string $address = null,
                                        string $email = null, string $phone_num = null, int $branchId = null,
                                        bool $isLeft = null, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($name) {
            $filters[] = "name LIKE :name";
            $placeholders['name'] = "%" . $name . "%";
        }
        if ($address) {
            $filters[] = "address LIKE :address";
            $placeholders['address'] = "%" . $address . "%";
        }
        if ($email) {
            $filters[] = "email LIKE :email";
            $placeholders['email'] = "%" . $email . "%";
        }
        if ($phone_num) {
            $filters[] = "phone_num LIKE :phone_num";
            $placeholders['phone_num'] = "%" . $phone_num . "%";
        }
        if ($branchId)
            $filters[] = "branch_id=$branchId";
        if ($isLeft != null)
            $filters[] = "is_left=$isLeft";

        $condition = null;
        if ($filters)
            $condition = implode(' AND ', $filters);
        $statement = self::getDataFromTable(['name', 'address', 'email', 'phone_num', 'branch_id', 'left_date'],
            'employees', $condition, $placeholders, ['employee_id', 'asc'], [$startingIndex, $limit]);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['left_date'] === null)
                $data[$i]['left_date'] = "Not Left";
        }
        return $data;
    }

    public static function deleteEmployee(int $employeeId): bool
    {
        $sql = "DELETE FROM employees WHERE employee_id=$employeeId";
        if (self::exec($sql))
            return true;
        return false;
    }
}