<?php

namespace LogicLeap\StockManagement\models;

use DateTime;
use PDO;

class User extends DbModel
{

    // Following constants need to be initialized. They are used when executing Database actions.

    private const TABLE_NAME = 'users';
    private const PRIMARY_KEY = 'id';
    private const TABLE_COLUMNS = ['id', 'username', 'email', 'firstname', 'lastname', 'password', 'role'];

    // User Roles
    public const ROLE_SUPER_ADMINISTRATOR = 0;
    public const ROLE_ADMINISTRATOR = 1;
    public const ROLE_CASHIER = 2;

    public int $userId;
    public string $username;
    public string $firstname;
    public string $lastname;
    public string $email;
    public string $role;


    /**
     * @param int $userId User id to search for
     * @return int Branch id if the user has a branch id. Returns "0" if user does not have a branch id.
     */
    public static function getUserBranchId(int $userId): int
    {
        $sql = "SELECT branch_id FROM " . self::TABLE_NAME . " WHERE id=$userId";
        $statement = self::prepare($sql);
        $statement->execute();
        $id = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$id)
            return 0;
        else
            return $id['id'];
    }

    /**
     * Generate a password hash.
     * @param string $password String Password
     * @return string Hashed password string
     */
    public static function generatePasswordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Password Requirements
    private const MAX_PASSWORD_LENGTH = 24;
    private const MIN_PASSWORD_LENGTH = 8;

    /**
     * Create a new user in the database.
     * @param array $params An array of [key=> value] pairs.
     * @return string Return 'user created.' if success, Error msg if failed
     */
    public function createNewUser(array $params): string
    {
        if (!isset($params['username']) || !isset($params['role']) || !isset($params['password'])) {
            return 'All required fields were not filled.';
        }

        // Performing checks on input variables.

        $statement = self::getDataFromTable(['id'], self::TABLE_NAME, 'username=:username',
            [':username' => $params['username']]);
        if ($statement->fetch(PDO::FETCH_ASSOC)) {
            return 'Username already exists.';
        }

        $statement = self::getDataFromTable(['id'], self::TABLE_NAME, 'email=:email',
            [':email' => $params['email']]);
        if ($statement->fetch(PDO::FETCH_ASSOC)) {
            return 'Email already exists.';
        }

        if (strlen($params['password']) < self::MIN_PASSWORD_LENGTH) {
            return 'Password is too short.';
        }

        if (strlen($params['password']) > self::MAX_PASSWORD_LENGTH) {
            return 'Password is too long.';
        }

        $params['password'] = self::generatePasswordHash($params['password']);

        //For now set user role to 1
        $params['role'] = self::ROLE_CASHIER;

        // Filter user passed variables against actual database available columns.
        foreach ($params as $key => $value) {
            if (!in_array($key, self::TABLE_COLUMNS)) {
                unset($params[$key]);
            }
        }
        if (self::insertIntoTable(self::TABLE_NAME, $params)) {
            return 'user created.';
        }
        return 'Unknown error occurred.';
    }

    /**
     * If passed user ID is present, set instance variable values.
     * @param int $userID User ID to look for
     * @return bool True if user is present, false otherwise.
     */
    public function loadUserData(int $userID): bool
    {
        $statement = self::getDataFromTable(['*'], 'users', "id=" . $userID);
        $userData = $statement->fetchAll()[0];
        if ($userData) {
            $this->userId = $userData['id'];
            $this->username = $userData['username'];
            $this->email = $userData['email'] ?? "";
            $this->firstname = $userData['firstname'] ?? "";
            $this->lastname = $userData['lastname'] ?? "";
            $this->role = $userData['role'];
            return true;
        }
        return false;
    }

    /**
     * Validate the user presence. If user is present set userId value of the instance to the userId.
     * @param string $username Username / Email of the user.
     * @param string $password Password of the user.
     * @return bool Return true if user exists, false if not.
     */
    public function validateUser(string $username, string $password): bool
    {
        $sql = "SELECT id, password FROM " . self::TABLE_NAME . " WHERE username=:username OR email=:username";
        $statement = self::prepare($sql);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            if (password_verify($password, $data['password'])) {
                $this->userId = $data['id'];
                return true;
            }
        }
        return false;
    }

    /**
     * Get user type text.
     * @return string Return user type message. 'none' if no role found.
     */
    public function getUserRoleText(): string
    {
        if ($this->userId == User::ROLE_ADMINISTRATOR)
            return 'admin';
        elseif ($this->userId == User::ROLE_CASHIER)
            return 'user';
        else
            return 'none';
    }

    public function markSuccessfulLogin(int $userId, string $authToken, string $idAddress): void
    {
        $time = new DateTime('now');
        $time = $time->format('Y-m-d H:i:s');

        $sql = "SELECT id FROM user_status WHERE user_id=$userId";
        $statement = self::prepare($sql);
        $statement->execute();
        if ($statement->fetch(PDO::FETCH_ASSOC)) {
            $sql = "UPDATE user_status SET auth_token='$authToken', last_active='$time', ip_addr='$idAddress' WHERE user_id=$userId";
        } else {
            $sql = "INSERT INTO user_status (user_id, auth_token, last_active, ip_addr) VALUES 
                                                                ($userId, '$authToken', '$time', '$idAddress')";
        }
        self::exec($sql);
    }
}