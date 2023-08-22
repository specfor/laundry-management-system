<?php

namespace LogicLeap\StockManagement\models\stock_management;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class PriceCategories extends DbModel
{
    private const TABLE_NAME = "price_categories";

    public static function addCategory(string $categoryName): bool|string|array
    {
        $params['name'] = strtolower($categoryName);
        $statement = self::getDataFromTable(['name'], self::TABLE_NAME, 'name=:name', $params);
        if ($statement->fetch(PDO::FETCH_ASSOC))
            return "There is '$categoryName' already added.";

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return false;
        else
            return ['category_id' => $id];
    }

    public static function updateCategory(int $categoryId, string $categoryName): bool
    {
        $params['name'] = strtolower($categoryName);
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

    public static function getCategories(int   $pageNumber = 0, string $categoryName = null, int $limit = 30,
                                         array $categoryIds = null): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($categoryName) {
            $filters[] = "name LIKE :name";
            $placeholders['name'] = strtolower($categoryName);
        }
        if ($categoryIds) {
            $filtersTemp = [];
            foreach ($categoryIds as $index => $categoryId) {
                $filtersTemp[] = "category_id=:category$index";
                $placeholders["category$index"] = $categoryId;
            }
            $filters[]=implode(' or ', $filtersTemp);
        }

        $condition = implode(' and ', $filters);
        $statement = self::getDataFromTable(['*'], self::TABLE_NAME, $condition, $placeholders,
            ['category_id', 'asc'], [$startingIndex, $limit]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}