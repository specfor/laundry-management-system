<?php

namespace LogicLeap\StockManagement\models;

class Customers extends DbModel
{
    private const TABLE_NAME = 'customers';

    public string $email;
    public string $firstname;
    public string $lastname;
    public string $phoneNumber;
    public string $address;

    public static function addNewCustomer(string $firstname, string $lastname, string $email,
                                   string $phoneNumber, string $address):bool
    {
        $sql = "INSERT INTO ".self::TABLE_NAME. " (email, firstname, lastname, phone_num, address) VALUES 
                (':email', ':firstname', ':lastname', ':phone_num', ':address')";
        $statement = self::prepare($sql);
        $statement->bindValue(':email', $email);
        $statement->bindValue(':firstname', $firstname);
        $statement->bindValue(':lastname', $lastname);
        $statement->bindValue(':phone_num', $phoneNumber);
        $statement->bindValue(':address', $address);
        return $statement->execute();
    }
}