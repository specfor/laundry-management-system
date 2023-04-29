<?php

namespace LogicLeap\StockManagement\models;

use PDO;

class Items extends DbModel
{
    private const TABLE_NAME = 'items';
    private static array $priceCategories;

    public static function addItem(string $itemName, array $prices, bool $blocked): bool
    {
        $params['name'] = strtolower($itemName);
        $params['blocked'] = $blocked;
        $params['price'] = '';

        foreach ($prices as $categoryName => $price) {
            $category = self::getPriceCategoryId($categoryName);
            if ($category == null)
                return false;
            $params['price'] .= $category . ":" . $price . ",";
        }
        return self::insertIntoTable(self::TABLE_NAME, $params);
    }

    public static function updateItem(int  $itemId, string $itemName = null, array $prices = null,
                                      bool $blocked = null): bool
    {
        $params = [];
        if ($itemName)
            $params['name'] = $itemName;
        if ($prices) {
            foreach ($prices as $categoryName => $price) {
                $category = self::getPriceCategoryId($categoryName);
                if ($category == null)
                    return false;
                $params['price'] .= $category . ":" . $price . ",";
            }
        }
        if ($blocked)
            $params['blocked'] = $blocked;

        return self::updateTableData(self::TABLE_NAME, $params, "item_id=$itemId");
    }

    public static function deleteItem(int $itemId): bool
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE item_id=$itemId";
        if (self::exec($sql))
            return true;
        return false;
    }

    public static function getItems(int  $pageNumber = 0, string $itemName = null, float $price = null,
                                    bool $blocked = null, $limit = 30)
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($itemName) {
            $filters[] = "name LIKE :name";
            $placeholders['name'] = "%" . $itemName . "%";
        }
        if ($price) {
            $filters[] = "price LIKE :price";
            $placeholders['price'] = "%" . $price . "%";
        }
        if ($blocked)
            $filters[] = "blocked=$blocked";

        $condition = implode(' AND ', $filters);
        $statement = self::getDataFromTable(['*'], self::TABLE_NAME, $condition, $placeholders, ['item_id', 'asc'], [$startingIndex, $limit]);
        $items = $statement->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($items); $i++) {
            $priceCategoriesWithPrices = explode(',', $items[$i]['price']);
            $newPrices = [];
            foreach ($priceCategoriesWithPrices as $priceCategory){
                $priceCategory = explode(':', $priceCategory);
                $priceCategoryName = self::getPriceCategoryName(intval($priceCategory[0]));
                $newPrices[$priceCategoryName] = $priceCategory[1];
            }
            $items[$i]['price'] = $newPrices;
        }
        return $items;
    }

    private static function getPriceCategoryId(string $categoryName): int|null
    {
        if (!isset(self::$priceCategories)) {
            $statement = self::getDataFromTable(['*'], 'price_categories');
            self::$priceCategories = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach (self::$priceCategories as $category) {
            if ($category['name'] == $categoryName)
                return $category['category_id'];
        }
        return null;
    }

    private static function getPriceCategoryName(int $categoryId): string{
        if (!isset(self::$priceCategories)) {
            $statement = self::getDataFromTable(['*'], 'price_categories');
            self::$priceCategories = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach (self::$priceCategories as $category) {
            if ($category['category_id'] == $categoryId)
                return $category['name'];
        }
        return 'UNKNOWN';
    }
}