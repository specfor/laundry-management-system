<?php

namespace LogicLeap\StockManagement\models;

use LogicLeap\StockManagement\core\Application;
use PDOException;
use PDOStatement;

abstract class DbModel
{
    /**
     * Prepare sql statement
     * @param string $sql SQL statement to prepare
     * @return false|PDOStatement PDOStatement if success, PDOException or false if any error occurred.
     */
    protected static function prepare(string $sql)
    {
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
     * @return bool True if success in inserting to table.False if any error.
     */
    protected static function insertIntoTable(string $tableName, array $params,
                                              string $condition = '', array $placeholderValues = []): bool
    {
        // Check whether all the keys passed here are real column names as user passed request data is passed to this.
        $attributes = [];
        $values = [];
        foreach ($params as $key => $value) {
            $attributes[] = $key;
            $values[] = $value;

        }
        $placeholders = array_map(fn($attr) => ":$attr", $attributes);
        $sql = "INSERT INTO $tableName (" . implode(',', $attributes) . ") VALUES (" .
            implode(',', $placeholders) . ")";
        if ($condition) {
            $sql .= " WHERE $condition";
        }
        $statement = self::prepare($sql);
        if (!empty($placeholderValues)) {
            foreach ($placeholderValues as $placeholder => $value) {
                $statement->bindValue($placeholder, $value);
            }
        }
        for ($i = 0; $i < count($placeholders); $i++) {
            $statement->bindValue($placeholders[$i], $values[$i]);
        }
        return $statement->execute();
    }

    /**
     * Retrieve data from the given table
     * @param array $rows Array of row names of which should be returned.
     * @param string $tableName Table name from where to get data.
     * @param string|null $conditionWithPlaceholders The condition to get data with placeholders(if needed) to values.
     *      Should be a valid sql condition.
     * @param array $placeholderValues Associative array of placeholder => value.
     * @param array|null $orderBy Array of [order_column, ASC|DESC]
     * @param int|array|null $limit Just number of rows to limit or array of [startingIndex, noOfRows]
     * @return mixed Return PDOStatement|PDOException|bool based on scenario.
     */
    protected static function getDataFromTable(array     $rows, string $tableName, string $conditionWithPlaceholders = null,
                                               array     $placeholderValues = [], array $orderBy = null,
                                               int|array $limit = null)
    {
        $sql = "SELECT " . implode(', ', $rows) . " FROM $tableName";
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

        $statement = self::prepare($sql);
        foreach ($data as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        if ($placeholderValues) {
            foreach ($placeholderValues as $placeholder => $value) {
                $statement->bindValue($placeholder, $value);
            }
        }
        return $statement->execute();
    }
}