<?php

namespace LogicLeap\StockManagement\models;

class Employees extends DbModel
{
    public static function addEmployee(string $name, string $address, string $email, string $phoneNumber,
                                       int $branchId, string $joinDate, string $left_date = null):bool
    {
        $isLeft = false;
        if ($left_date){
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

    public static function updateEmployee(string $name = null, string $address = null, string $email = null,
                                          string $phoneNumber = null, int $branchId = null, string $joinDate = null,
                                          string $left_date = null)
    {
        $sql = "UPDATE TABLE employees ";
    }
}