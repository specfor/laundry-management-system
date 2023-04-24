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
    public const ROLE_MANAGER = 2;
    public const ROLE_CASHIER = 3;

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
        if ($id['branch_id'] == null)
            return 0;
        else
            return $id['branch_id'];
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
    private const MIN_USERNAME_LENGTH = 6;
    private const MAX_USERNAME_LENGTH = 30;

    public static function createNewUser(string $username, string $password, string $role, string $email = null,
                                         string $firstname = null, string $lastname = null, int $branchId = null): string
    {
        // Performing checks on input variables.

        $statement = self::getDataFromTable(['id'], self::TABLE_NAME, 'username=:username',
            [':username' => $username]);
        if ($statement->fetch(PDO::FETCH_ASSOC)) {
            return 'Username already exists.';
        }

        if ($email) {
            $statement = self::getDataFromTable(['id'], self::TABLE_NAME, 'email=:email',
                [':email' => $email]);
            if ($statement->fetch(PDO::FETCH_ASSOC)) {
                return 'Email already exists.';
            }
        }

        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return 'Password is too short.';
        }

        if (strlen($password) > self::MAX_PASSWORD_LENGTH) {
            return 'Password is too long.';
        }

        if (strlen($username) < self::MIN_USERNAME_LENGTH) {
            return 'Username is too short.';
        }

        if (strlen($username) > self::MAX_USERNAME_LENGTH) {
            return 'Username is too long.';
        }

        $usernameRegEx = '^[A-Za-z][A-Za-z0-9_]{5,29}$';
        if (!preg_match($usernameRegEx, $username))
            return 'Username should only contain english letters, numbers and underscore(_)';

        // Associative array of [table_column_name => value]
        $params = [];

        if (strtolower($role) == 'administrator')
            $params['role'] = self::ROLE_ADMINISTRATOR;
        elseif (strtolower($role) == 'manager')
            $params['role'] = self::ROLE_MANAGER;
        elseif (strtolower($role == 'cashier'))
            $params['role'] = self::ROLE_CASHIER;
        else
            return 'Invalid user role provided.';

        $params['password'] = self::generatePasswordHash($params['password']);

        if ($email)
            $params['email'] = $email;
        if ($firstname)
            $params['firstname'] = $firstname;
        if ($lastname)
            $params['lastname'] = $lastname;
        if ($branchId)
            $params['branch_id'] = $branchId;

        if (self::insertIntoTable(self::TABLE_NAME, $params)) {
            return 'New user created successfully.';
        }
        return 'Failed to create new user.';
    }

    public static function getUsers(int $startingIndex = 0, int $limit = 30): array
    {
        $superAdminRole = self::ROLE_SUPER_ADMINISTRATOR;
        $sql = "SELECT * FROM users WHERE role!=$superAdminRole LIMIT $startingIndex, $limit";
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
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

    public static function getNumberOfUsers(int $userRole):int
    {
        $sql = "SELECT id FROM users WHERE role=$userRole";
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement->rowCount();
    }

    /**
     * Get user type text.
     * @return string Return user type message. 'none' if no role found.
     */
    public function getUserRoleText(): string
    {
        if ($this->role == User::ROLE_ADMINISTRATOR)
            return 'administrator';
        elseif ($this->role == User::ROLE_CASHIER)
            return 'cashier';
        elseif ($this->role == User::ROLE_MANAGER)
            return 'manager';
        else
            return 'none';
    }

    public static function getUserRole(int $userId): bool
    {
        $sql = "SELECT role FROM users WHERE id=$userId";
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC)['role'];
    }

    public static function deleteUser(int $userId): bool
    {
        $sql = "DELETE FROM users WHERE id=$userId";
        if (self::exec($sql))
            return true;
        return false;
    }
}