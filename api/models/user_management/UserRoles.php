<?php

namespace LogicLeap\StockManagement\models\user_management;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class UserRoles extends DbModel
{
    private const TABLE_NAME = 'user_roles';

    public static function getUserRoles(int   $pageNumber = 0, int $roleId = null, string $name = null,
                                        array $permissions = null, string $description = null, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];

        if ($roleId)
            $filters[] = 'role_id=' . $roleId;
        if ($name) {
            $filters[] = 'name LIKE :name';
            $placeholders['name'] = strtolower($name) . "%";
        }
        if ($permissions) {
            //TODO filter by permission does not work well this way. Fix it.
            $filters[] = 'permissions=:permissions';
            foreach ($permissions as $group => &$permissions_) {
                foreach ($permissions_ as &$permission) {
                    $permission = User::getPermissionId($permission);
                }
            }
            $placeholders['permissions'] = json_encode($permissions);
        }
        if ($description) {
            $filters[] = 'description LIKE :description';
            $placeholders['description'] = "%" . $description . "%";
        }

        $condition = null;
        if ($filters)
            $condition = implode(' AND ', $filters);

        $statement = self::getDataFromTable(['role_id', 'name', 'description', 'permissions'],
            self::TABLE_NAME, $condition, $placeholders, ['role_id', 'asc'], [$startingIndex, $limit]);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as &$role) {
            $role['permissions'] = json_decode($role['permissions'], flags: JSON_OBJECT_AS_ARRAY);

            if (array_key_exists('all', $role['permissions']))
                $role['permissions'] = User::PERMISSIONS;
            foreach ($role['permissions'] as $permissionGroup => &$permissions_) {
                foreach ($permissions_ as &$permission) {
                    $permission = User::getPermissionText($permission);
                }
            }
        }
        $count = self::countTableRows(self::TABLE_NAME, $condition, $placeholders);
        return [$data, $count];
    }

    public static function createUserRole(string $name, array $permissions, string $description = null): bool|array|string
    {
        $name = strtolower($name);
        $statement = self::getDataFromTable(['role_id'], self::TABLE_NAME, 'name=:name',
            ['name' => $name]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($data))
            return "User role with name '$name' already exists.";

        $params['name'] = $name;
        $params['description'] = $description;

        $keys = array_keys(User::PERMISSIONS);

        foreach ($permissions as $type => &$permissions_) {
            if (!is_array($permissions_))
                return "Permission structure is invalid.";

            if (empty($permissions_))
                unset($permissions[$type]);

            if (!in_array($type, $keys))
                return "Invalid permission groups were sent.";

            foreach ($permissions_ as &$permission) {
                $permission = User::getPermissionId($permission);
                if (!in_array($permission, User::PERMISSIONS[$type]))
                    return "Invalid permissions were sent.";
            }
        }

        $params['permissions'] = json_encode($permissions);
        $params['locked'] = false;

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return false;
        else
            return ['user-role-id' => $id];
    }

    public static function updateUserRole(int    $roleId, string $name = null, array $permissions = null,
                                          string $description = null): bool|string
    {
        if (empty($name) && empty($permissions) && empty($description))
            return "No values were passed to update.";

        $statement = self::getDataFromTable(['locked'], self::TABLE_NAME, "role_id=$roleId");
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data['locked'])
            return "This user role can not be updated.";

        $name = strtolower($name);
        $statement = self::getDataFromTable(['role_id'], self::TABLE_NAME, 'name=:name',
            ['name' => $name]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($data) && $data['role_id'] !== $roleId)
            return "User role with name '$name' already exists.";

        if ($name)
            $params['name'] = $name;
        if ($description)
            $params['description'] = $description;
        if ($permissions) {
            $keys = array_keys(User::PERMISSIONS);

            foreach ($permissions as $type => &$permissions_) {
                if (!is_array($permissions_))
                    return "Permission structure is invalid.";

                if (!in_array($type, $keys))
                    return "Invalid permission groups were sent.";

                foreach ($permissions_ as &$permission) {
                    $permission = User::getPermissionId($permission);
                    if (!in_array($permission, User::PERMISSIONS[$type]))
                        return "Invalid permissions were sent.";
                }
            }
            $params['permissions'] = json_encode($permissions);
        }

        return self::updateTableData(self::TABLE_NAME, $params, "role_id=$roleId");
    }

    public static function removeUserRole(int $roleId): bool|string
    {
        $statement = self::getDataFromTable(['locked'], self::TABLE_NAME,
            "role_id=$roleId");
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($data))
            return "Invalid user-role ID.";
        if ($data['locked'])
            return "This user-role can not be removed.";

        return self::removeTableData(self::TABLE_NAME, "role_id=$roleId");
    }


    /**
     * Get user type text.
     * @return string Return user type message. 'none' if no role found.
     */
    public static function getUserRoleText(int $role): string
    {
        $userRole = self::getUserRoles(roleId: $role);
        if (empty($userRole))
            return 'NO ROLE';
        return $userRole[0]['name'];
    }
}
