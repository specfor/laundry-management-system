<?php

namespace LogicLeap\StockManagement\models\user_management;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class User extends DbModel
{

    // Following constants need to be initialized. They are used when executing Database actions.

    private const TABLE_NAME = 'users';

    // User Roles
    public const ROLE_SUPER_ADMINISTRATOR = 0;
    public const PERMISSION_READ = 0;
    public const PERMISSION_WRITE = 1;
    public const PERMISSION_MODIFY = 3;
    public const PERMISSION_DELETE = 4;

    public const PERMISSIONS = [
        'users' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'user-roles' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'customers' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'branches' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'employees' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'products' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'categories' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'orders' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'payments' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'tax' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
        'financial_accounts' => [self::PERMISSION_WRITE, self::PERMISSION_READ, self::PERMISSION_MODIFY, self::PERMISSION_DELETE],
    ];

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

    public static function createNewUser(
        string $username, string $password, string $role, string $email = null,
        string $firstname = null, string $lastname = null, int $branchId = null
    ): array|string
    {
        // Performing checks on input variables.

        $statement = self::getDataFromTable(
            ['id'], self::TABLE_NAME,
            'username=:username',
            [':username' => $username]
        );
        if ($statement->fetch(PDO::FETCH_ASSOC)) {
            return 'Username already exists.';
        }

        if ($email) {
            $statement = self::getDataFromTable(
                ['id'], self::TABLE_NAME,
                'email=:email',
                [':email' => $email]
            );
            if ($statement->fetch(PDO::FETCH_ASSOC)) {
                return 'Email already exists.';
            }
        }

        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            return 'Password should be at least ' . self::MIN_PASSWORD_LENGTH . ' characters long.';
        }

        if (strlen($password) > self::MAX_PASSWORD_LENGTH) {
            return 'Password should not be longer than ' . self::MAX_PASSWORD_LENGTH . ' characters.';
        }

        if (strlen($username) < self::MIN_USERNAME_LENGTH) {
            return 'Username should be at least ' . self::MIN_USERNAME_LENGTH . ' characters long.';
        }

        if (strlen($username) > self::MAX_USERNAME_LENGTH) {
            return 'Password should not be longer than ' . self::MAX_USERNAME_LENGTH . ' characters.';
        }

        $usernameRegEx = '/^[A-Za-z][A-Za-z0-9_]{5,29}$/';
        if (!preg_match($usernameRegEx, $username))
            return 'Username should only contain english letters, numbers and underscore(_)';

        // Associative array of [table_column_name => value]
        $params = [];

        if ($email)
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                return 'Provided email address is invalid.';
        $params['email'] = $email;

        $userRoles = UserRoles::getUserRoles(limit: 1000);

        $roleId = null;
        foreach ($userRoles as $userRole) {
            if ($userRole['name'] == strtolower($role)) {
                $roleId = $userRole['role_id'];
                break;
            }
        }
        if ($roleId === null)
            return 'Invalid user role provided.';

        $params['role'] = $roleId;
        $params['username'] = $username;
        $params['password'] = self::generatePasswordHash($password);

        if ($firstname)
            $params['firstname'] = $firstname;
        if ($lastname)
            $params['lastname'] = $lastname;
        if ($branchId)
            $params['branch_id'] = $branchId;

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false) {
            return 'Failed to create new user.';
        }
        return ['message' => 'New user created successfully.', 'user-id' => $id];
    }

    public static function getUsers(
        int    $pageNumber = 0, string $username = null, string $name = null, string $email = null,
        string $role = null, int $branchId = null, int $limit = 30, int $userId = null
    ): array
    {
        $superAdminRole = self::ROLE_SUPER_ADMINISTRATOR;
        $startingIndex = $pageNumber * $limit;

        $condition = "role!=$superAdminRole";

        $placeholders = [];
        if ($userId)
            $condition .= " AND id=$userId";
        if ($username) {
            $condition .= " AND username LIKE :username";
            $placeholders['username'] = "%" . $username . "%";
        }
        if ($name) {
            $condition .= " AND firstname LIKE :name OR lastname LIKE :name";
            $placeholders['name'] = "%" . $name . "%";
        }
        if ($email) {
            $condition .= " AND email LIKE :email";
            $placeholders['email'] = "%" . $email . "%";
        }
        if ($role) {
            $roleId = null;
            $userRoles = UserRoles::getUserRoles(limit: 1000);

            foreach ($userRoles as $userRole) {
                if ($userRole['name'] == strtolower($role)) {
                    $roleId = $userRole['role_id'];
                    break;
                }
            }
            if ($roleId)
                $condition .= " AND role=$role";
        }
        if ($branchId)
            $condition .= " AND branch_id=$branchId";

        $statement = self::getDataFromTable(
            ['id', 'username', 'firstname', 'lastname', 'role', 'branch_id', 'email'],
            'users',
            $condition,
            $placeholders,
            ['id', 'asc'],
            [$startingIndex, $limit]
        );
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['role'] = UserRoles::getUserRoleText($data[$i]['role']);
        }
        $count = self::countTableRows(self::TABLE_NAME, $condition, $placeholders);
        return [$data, $count];
    }

    public static function updateUser(
        int    $userId, string $password = null, string $role = null,
        string $email = null, string $firstname = null,
        string $lastname = null, int $branchId = null
    ): bool|string
    {
        $updateFields = [];

        if ($password)
            $updateFields['password'] = self::generatePasswordHash($password);
        if ($role) {
            $userRoles = UserRoles::getUserRoles(limit: 1000);

            $roleId = null;
            foreach ($userRoles as $userRole) {
                if ($userRole['name'] == strtolower($role)) {
                    $roleId = $userRole['role_id'];
                    break;
                }
            }
            if ($roleId === null)
                return 'Invalid user role provided.';
            $updateFields['role'] = $roleId;
        }
        if ($email)
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                return "Provided email is invalid.";
        $updateFields['email'] = $email;
        if ($firstname)
            $updateFields['firstname'] = $firstname;
        if ($lastname)
            $updateFields['lastname'] = $lastname;
        if ($branchId)
            $updateFields['branch_id'] = $branchId;

        if (self::updateTableData('users', $updateFields, "id=$userId"))
            return true;
        return false;
    }

    public static function getUserLoginHistory(int $userId, string $date = null, string $ipAddress = null,
                                               int $pageNumber = 0, int $limit = 30): array
    {
        $startingIndex = $limit * $pageNumber;
        $filters = [];
        $placeholders = [];

        $filters[]="user_id=$userId";
        if ($date) {
            $filters[] = "logged_at like :date";
            $placeholders['date'] = "$date%";
        }
        if ($ipAddress) {
            $filters[] = "ip_addr like :ip";
            $placeholders['ip'] = $ipAddress;
        }

        $condition = implode(' AND ', $filters);
        $data = self::getDataFromTable(['id', 'ip_addr','logged_at'], 'user_status', $condition, $placeholders, ['id', 'desc'],
            [$startingIndex, $limit])->fetchAll(PDO::FETCH_ASSOC);
        $count = self::countTableRows('user_status', $condition, $placeholders);
        return [$data, $count];
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

    public static function getNumberOfUsers(int $userRole): int
    {
        $sql = "SELECT id FROM users WHERE role=$userRole";
        $statement = self::prepare($sql);
        $statement->execute();
        return $statement->rowCount();
    }


    public static function getUserRole(int $userId): int
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

    public static function getPermissionText(int $permission): string
    {
        return match ($permission) {
            self::PERMISSION_READ => 'read',
            self::PERMISSION_WRITE => 'write',
            self::PERMISSION_MODIFY => 'modify',
            self::PERMISSION_DELETE => 'delete',
            default => 'INVALID PERMISSION',
        };
    }

    public static function getPermissionId(string $permission): int
    {
        return match ($permission) {
            'read' => self::PERMISSION_READ,
            'write' => self::PERMISSION_WRITE,
            'modify' => self::PERMISSION_MODIFY,
            'delete' => self::PERMISSION_DELETE,
            default => '-1'
        };
    }

    public static function getAllPermissions(): array
    {
        $tmpPerms = self::PERMISSIONS;
        foreach ($tmpPerms as $type => &$perms) {
            foreach ($perms as &$perm) {
                $perm = self::getPermissionText($perm);
            }
        }
        return $tmpPerms;
    }
}