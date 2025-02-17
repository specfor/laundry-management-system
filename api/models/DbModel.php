<?php

namespace LogicLeap\StockManagement\models;

use LogicLeap\PhpServerCore\Application;
use PDO;
use PDOException;
use PDOStatement;

abstract class DbModel
{
    /**
     * Prepare sql statement
     * @param string $sql SQL statement to prepare
     * @return false|PDOStatement PDOStatement if success, PDOException or false if any error occurred.
     */
    protected static function prepare(string $sql, bool $beginTransaction = false)
    {
        if ($beginTransaction)
            Application::$app->db->pdo->beginTransaction();
        return Application::$app->db->pdo->prepare($sql);
    }

    protected static function exec(string $sql)
    {
        return Application::$app->db->pdo->exec($sql);
    }

    /**
     * Insert data into table. There must be a <b>TABLE_NAME</b> constant defining the relevant table name
     * in the Class definition.
     * @param string $tableName Name of the table where to insert data.
     * @param array $params An array of [column-name => value-belong-to-the-column] pairs.
     * @param string $condition SQL condition to insert data. Placeholders can be added if it is needed
     *      to use prepared statements.
     * @param array $placeholderValues If passed a condition with placeholders, associative array of [placeholder => value]
     *      need to be passed.
     * @return bool|int Last_insert_id if success in inserting to table.False if any error.
     */
    protected static function insertIntoTable(string $tableName, array $params,
                                              string $condition = '', array $placeholderValues = []): bool|int
    {
        // Check whether all the keys passed here are real column names as user passed request data is passed to this.
        $attributes = [];
        $values = [];
        foreach ($params as $key => $value) {
            $attributes[] = $key;
            if ($value === true)
                $value = 1;
            elseif ($value === false)
                $value = 0;
            $values[] = $value;

        }
        $placeholders = array_map(fn($attr) => ":$attr", $attributes);
        $sql = "INSERT INTO $tableName (" . implode(',', $attributes) . ") SELECT " .
            implode(',', $placeholders);
        if ($condition) {
            $sql .= " WHERE $condition";
        }
        $statement = self::prepare($sql, true);
        if (!empty($placeholderValues)) {
            foreach ($placeholderValues as $placeholder => $value) {
                if ($value === true)
                    $value = 1;
                elseif ($value === false)
                    $value = 0;
                $statement->bindValue($placeholder, $value);
            }
        }
        for ($i = 0; $i < count($placeholders); $i++) {
            $statement->bindValue($placeholders[$i], $values[$i]);
        }
        if ($statement->execute() === false) {
            Application::$app->db->pdo->rollBack();
            return false;
        } else {
            $lastInsertId = Application::$app->db->pdo->lastInsertId();
            Application::$app->db->pdo->commit();
            return $lastInsertId;
        }
    }

    /**
     * Retrieve data from the given table
     * @param array $columns Array of column names of which should be returned.
     * @param string $tableName Table name from where to get data.
     * @param string|null $conditionWithPlaceholders The condition to get data with placeholders(if needed) to values.
     *      Should be a valid sql condition.
     * @param array $placeholderValues Associative array of placeholder => value.
     * @param array|null $orderBy Array of [order_column, ASC|DESC]
     * @param int|array|null $limit Just number of rows to limit or array of [startingIndex, noOfRows]
     * @return mixed Return PDOStatement|PDOException|bool based on scenario.
     */
    protected static function getDataFromTable(array     $columns, string $tableName, string $conditionWithPlaceholders = null,
                                               array     $placeholderValues = [], array $orderBy = null,
                                               int|array $limit = null)
    {
        $sql = "SELECT " . implode(', ', $columns) . " FROM $tableName";
        if ($conditionWithPlaceholders)
            $sql .= " WHERE $conditionWithPlaceholders";
        if ($orderBy)
            $sql .= " ORDER BY $orderBy[0] $orderBy[1]";
        if ($limit)
            if (is_array($limit))
                $sql .= " LIMIT $limit[0], $limit[1]";
            else
                $sql .= " LIMIT $limit";

        $statement = self::prepare($sql);
        if ($conditionWithPlaceholders && !empty($placeholderValues)) {
            foreach ($placeholderValues as $placeholder => $value) {
                if ($value === true)
                    $value = 1;
                elseif ($value === false)
                    $value = 0;
                $statement->bindValue($placeholder, $value);
            }
        }
        $statement->execute();
        return $statement;
    }

    /**
     * Updates data in a table.
     * @param string $tableName Name of the table.
     * @param array $data Associative array of [column-name => value]. Values ara passed through prepared statements
     * @param string $condition If needed, a condition can be passed. Can parse condition with placeholders
     * @param array $placeholderValues An Associative array of [placeholder => value] for the condition if condition is
     *      passed with placeholders.
     * @return bool True if success, false if failed.
     */
    protected static function updateTableData(string $tableName, array $data,
                                              string $condition = '', array $placeholderValues = []): bool
    {
        $columnsWithPlaceholders = [];
        foreach ($data as $key => $value) {
            $columnsWithPlaceholders[] = "$key=:$key";
        }
        $sql = "UPDATE $tableName SET " . implode(', ', $columnsWithPlaceholders);
        if ($condition) {
            $sql .= " WHERE $condition";
        }

        $statement = self::prepare($sql, true);
        foreach ($data as $key => $value) {
            if ($value === true)
                $value = 1;
            elseif ($value === false)
                $value = 0;
            $statement->bindValue(":$key", $value);
        }
        if ($placeholderValues) {
            foreach ($placeholderValues as $placeholder => $value) {
                if ($value === true)
                    $value = 1;
                elseif ($value === false)
                    $value = 0;
                $statement->bindValue($placeholder, $value);
            }
        }
        if ($statement->execute() === false) {
            Application::$app->db->pdo->rollBack();
            return false;
        } else {
            Application::$app->db->pdo->commit();
            return true;
        }
    }

    /**
     * Remove data from a table.
     * @param string $tableName Name of the table.
     * @param string $condition condition to remove data. May contain placeholders.
     * @param array $placeholders An Associative array of [placeholder => value] for the condition if condition is
     *      passed with placeholders.
     * @return bool True if success, false if failed.
     */
    protected static function removeTableData(string $tableName, string $condition, array $placeholders = []): bool
    {
        $sql = "DELETE FROM $tableName WHERE $condition";
        $statement = self::prepare($sql);
        if ($placeholders)
            foreach ($placeholders as $placeholder => $value) {
                if ($value === true)
                    $value = 1;
                elseif ($value === false)
                    $value = 0;
                $statement->bindValue($placeholder, $value);
            }
        return $statement->execute();
    }

    /**
     * Get the total number of records in the table.
     * @param string $tableName Name of the table
     * @param string|null $condition Condition to filter records.
     * @param array|null $conditionPlaceholders If condition contains any placeholders, they should be passed
     *          as [placeholder => value, ...]
     * @return int Number of records in the table.
     */
    protected static function countTableRows(string $tableName, string $condition = null, array $conditionPlaceholders = null): int
    {
        $sql = "select count(*) as 'count' from $tableName";
        if ($condition)
            $sql.= " WHERE $condition";
        $statement = self::prepare($sql);

        if ($conditionPlaceholders)
            foreach ($conditionPlaceholders as $placeholder => $value) {
                if ($value === true)
                    $value = 1;
                elseif ($value === false)
                    $value = 0;
                $statement->bindValue($placeholder, $value);
            }
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC)['count'];
    }
}