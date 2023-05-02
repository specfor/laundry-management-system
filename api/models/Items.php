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

        // $prices => [[categ_1, categ_2], 350.00]

        $categories = [];
        foreach ($prices[0] as $categoryName) {
            $categoryId = self::getPriceCategoryId($categoryName);
            if ($categoryId == null)
                return false;
            $categories[] = $categoryId;
        }
        $params['category_ids'] = implode(',', $categories);

        $statement = self::getDataFromTable(['name'], self::TABLE_NAME,
            'name=:name AND category_ids=:category_ids`', $params);
        if ($statement->fetch(PDO::FETCH_ASSOC))
            return false;


        if (!is_float($prices[1]) && !is_int($prices[1]))
            return false;
        $params['price'] = $prices[1];
        $params['blocked'] = $blocked;

        return self::insertIntoTable(self::TABLE_NAME, $params);
    }

    public static function updateItem(int  $itemId, string $itemName = null, array $prices = null,
                                      bool $blocked = null): bool
    {
        $params = [];
        if ($itemName)
            $params['name'] = strtolower($itemName);
        if ($prices) {
            foreach ($prices as $priceCombination) {
                $categories = [];
                foreach ($priceCombination[0] as $priceCategoryName) {
                    $category = self::getPriceCategoryId($priceCategoryName);

                    if ($category == null)
                        return false;
                    $categories[] = $category;
                }
                $params['price'] .= implode('&', $categories) . ":" . $priceCombination[1] . ",";
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
            $placeholders['name'] = "%" . strtolower($itemName) . "%";
        }
        if ($price) {
            $filters[] = "price LIKE :price";
            $placeholders['price'] = "%" . $price . "%";
        }
        if ($blocked)
            $filters[] = "blocked=$blocked";

        $condition = implode(' AND ', $filters);
        $statement = self::getDataFromTable(['*'], self::TABLE_NAME, $condition, $placeholders,
            ['item_id', 'asc'], [$startingIndex, $limit]);
        $items = $statement->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($items); $i++) {
            $items[$i]['blocked'] = boolval($items[$i]['blocked']);
            $priceCategoryIds = explode(',', $items[$i]['category_ids']);

            $priceCategoryNames = [];
            foreach ($priceCategoryIds as $priceCategoryId) {
                $priceCategoryName = self::getPriceCategoryName(intval($priceCategoryId));

                if ($priceCategoryName === "UNKNOWN")
                    continue;
                $priceCategoryNames[] = $priceCategoryName;
            }
            $items[$i]['categories'] = $priceCategoryNames;
            unset($items[$i]['category_ids']);
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

    private static function getPriceCategoryName(int $categoryId): string
    {
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