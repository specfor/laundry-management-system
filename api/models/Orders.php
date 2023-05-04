<?php

namespace LogicLeap\StockManagement\models;

use DateTime;
use PDO;

class Orders extends DbModel
{
    public const STATUS_ORDER_ADDED = 0;
    public const STATUS_PENDING_PAYMENT = 1;
    public const STATUS_PAYMENT_COMPLETED = 2;
    public const STATUS_PROCESSING = 3;
    public const STATUS_COMPLETED_PROCESSING = 4;
    public const STATUS_COMPLETED = 5;
    public const STATUS_ON_HOLD = 6;
    public const STATUS_REJECTED = 7;
    public const STATUS_CANCELLED = 8;

    public static function addNewOrder(array $items, float $totalPrice = null, int $branchId = null,
                                       int   $orderStatus = self::STATUS_ORDER_ADDED): bool|string
    {
        $itemIds = [];
        foreach ($items as $itemId => $amount) {
            if (!is_int($itemId))
                return "All item ids must be integers.";
            if (!is_int($amount))
                return "All amounts should be integers";
            if ($amount < 1)
                return "All amounts must be greater than 0";
            $itemIds[] = "item_id=$itemId";
        }
        $condition = "(" . implode(' OR ', $itemIds) . ")";
        $condition .= " AND blocked=false";
        $itemData = (self::getDataFromTable(['price', 'item_id'], 'items', $condition))->fetchAll(PDO::FETCH_ASSOC);

        if (count($itemData) < count($items))
            return "Invalid item ids were sent";

        $calculatedTotalPrice = 0;
        $newItemData = [];
        foreach ($items as $itemId => $amount) {
            foreach ($itemData as $item) {
                if ($itemId == $item['item_id']) {
                    $item['amount'] = $amount;
                    $calculatedTotalPrice += intval($amount)*floatval($item['price']);
                    $newItemData[] = $item;
                }
            }
        }
        $newItemData = json_encode($newItemData);

        if ($totalPrice)
            $params['total_price'] = $totalPrice;
        else
            $params['total_price'] = $calculatedTotalPrice;
        $params['items'] = $newItemData;
        $params['added_date'] = (new DateTime('now'))->format("Y-m-d");
        $params['status'] = $orderStatus;
        $params['branch_id'] = $branchId;

        return self::insertIntoTable('orders', $params);
    }
}