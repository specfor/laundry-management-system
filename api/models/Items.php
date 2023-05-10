<?php

namespace LogicLeap\StockManagement\models;

use PDO;

class Items extends DbModel
{
    private const TABLE_NAME = 'items';
    private static array $priceCategories;

    public static function addItem(string $itemName, array $prices, bool $blocked): bool|string|array
    {
        $params['name'] = strtolower($itemName);

        // $prices => [[categ_1, categ_2], 350.00]

        $categories = [];
        if (!is_array($prices[0]))
            return "Price categories must be an array.";
        foreach ($prices[0] as $categoryName) {
            $categoryId = self::getPriceCategoryId($categoryName);
            if ($categoryId == null)
                return "No category with name '$categoryName'";
            $categories[] = $categoryId;
        }
        $params['category_ids'] = implode(',', $categories);

        $statement = self::getDataFromTable(['name'], self::TABLE_NAME,
            'name=:name AND category_ids=:category_ids', $params);
        if ($statement->fetch(PDO::FETCH_ASSOC))
            return "There is already an item with provided name and categories.";


        if (!is_float($prices[1]) && !is_int($prices[1]))
            return "Price has to be either decimal or integer.";
        $params['price'] = $prices[1];
        $params['blocked'] = $blocked;

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return false;
        else
            return ['item_id' => $id];
    }

    public static function updateItem(int  $itemId, string $itemName = null, array $prices = null,
                                      bool $blocked = null): bool|string
    {
        $statement = self::getDataFromTable(['name', 'category_ids'], self::TABLE_NAME,
            "item_id=$itemId");
        $savedData = $statement->fetch(PDO::FETCH_ASSOC);

        $params = [];
        if ($itemName) {
            $params['name'] = strtolower($itemName);
            $conditionPayload['name'] = strtolower($itemName);
        }
        if ($prices) {
            $categories = [];
            if (is_string($prices[0]))
                $prices[0] = array($prices[0]);
            foreach ($prices[0] as $priceCategoryName) {
                $category = self::getPriceCategoryId($priceCategoryName);

                if ($category == null)
                    return "No category with name '$priceCategoryName'";
                $categories[] = $category;
            }
            $params['category_ids'] = implode(',', $categories);
            $conditionPayload['category_ids'] = $params['category_ids'];
            $params['price'] = $prices[1];
        }

        if ($itemName && $prices)
            $condition = "name=:name AND category_ids=:category_ids";
        elseif ($itemName)
            $condition = "name=:name";
        elseif ($prices)
            $condition = "category_ids=:category_ids";

        $data = (self::getDataFromTable(['name', 'category_ids'], self::TABLE_NAME,
            $condition, $conditionPayload))->fetch(PDO::FETCH_ASSOC);
        if ($data)
            return "There is already an item with name '" . $data['name'] . "' and  same categories.";

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

    public static function getItems(int   $pageNumber = 0, int $itemId = null, string $itemName = null,
                                    float $price = null, bool $blocked = null, $limit = 30): array
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

        $itemCount = count($items);
        for ($i = 0; $i < $itemCount; $i++) {
            $items[$i]['blocked'] = boolval($items[$i]['blocked']);
            $priceCategoryIds = explode(',', $items[$i]['category_ids']);

            $priceCategoryNames = [];
            $validItem = true;
            foreach ($priceCategoryIds as $priceCategoryId) {
                $priceCategoryName = self::getPriceCategoryName(intval($priceCategoryId));

                if ($priceCategoryName === "UNKNOWN") {
                    $validItem = false;
                    break;
                }
                $priceCategoryNames[] = $priceCategoryName;
            }
            if ($validItem) {
                $items[$i]['categories'] = $priceCategoryNames;
                unset($items[$i]['category_ids']);
            } else
                unset($items[$i]);
        }
        return array_values($items);
    }

    private static function getPriceCategoryId(string $categoryName): int|null
    {
        if (!isset(self::$priceCategories)) {
            $statement = self::getDataFromTable(['*'], 'price_categories', orderBy: ['category_id', 'asc']);
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
            $statement = self::getDataFromTable(['*'], 'price_categories', orderBy: ['category_id', 'asc']);
            self::$priceCategories = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        foreach (self::$priceCategories as $category) {
            if ($category['category_id'] == $categoryId)
                return $category['name'];
        }
        return 'UNKNOWN';
    }
}