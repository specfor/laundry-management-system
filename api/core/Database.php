<?php

namespace LogicLeap\PhpServerCore;

use PDO;
use PDOException;

class Database
{
    protected static string $servername;
    protected static string $username;
    protected static string $password;
    protected static string $dbName;

    public PDO $pdo;


    function __construct($servername, $username, $password, $dbName)
    {
        self::$servername = $servername;
        self::$username = $username;
        self::$password = $password;
        self::$dbName = $dbName;

        try {
            // Try to connect to mysql service.
            $this->pdo = new PDO("mysql:host=$servername", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (PDOException $e) {
            echo "Failed to connect to the database server";
            exit();
        }

        try {
           $this->connectDatabase();
        } catch (PDOException $e) {
            echo "Failed to connect to the database";
            exit();
        }
    }

    /**
     * Set class instance pdo object to the pdo object with database connection.
     */
    protected function connectDatabase(): void
    {
        // Try to connect to the relevant database.
        $this->pdo = new PDO("mysql:host=".self::$servername.";dbname=" . self::$dbName,
            self::$username, self::$password);
    }
}
