<?php

namespace LogicLeap\StockManagement\models;

class PriceCategories extends DbModel
{
    private const TABLE_NAME = "price_categories";

    public static function addCategory(string $categoryName): bool
    {
        $params['name'] = $categoryName;

        return self::insertIntoTable(self::TABLE_NAME, $params);
    }

    public static function updateCategory(int $categoryId, string $categoryName = null): bool
    {
        if (!$categoryName)
            return false;
        $params['name'] = $categoryName;
        $condition = "category_id=$categoryId";

        return self::updateTableData(self::TABLE_NAME, $params, $condition);
    }

    public static function deleteCategory(int $categoryId): bool
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE category_id=$categoryId";
        if (self::exec($sql))
            return true;
        return false;
    }

    public static function getCategories(int $pageNumber = 0, string $categoryName = null, int $limit = 30)
    {
        $startingIndex = $pageNumber * $limit;
        $condition = null;
        $placeholders = [];
        if ($categoryName) {
            $condition = "name LIKE :name";
            $placeholders['name'] = $categoryName;
        }
        return self::getDataFromTable(['*'], self::TABLE_NAME, $condition, $placeholders,
            ['category_id', 'asc'], [$startingIndex, $limit]);
    }
}