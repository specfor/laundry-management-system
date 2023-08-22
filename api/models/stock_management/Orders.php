<?php

namespace LogicLeap\StockManagement\models\stock_management;

use DateInterval;
use DateTime;
use LogicLeap\StockManagement\models\DbModel;
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

    public static function addNewOrder(array  $items, int $customerId, float $totalPrice = null, int $branchId = null,
                                       string $comments = null, string $orderStatus = "order added"): bool|string|array
    {
        if (empty(Customers::getCustomers($customerId)))
            return "Invalid customer-id";
        else
            $params['customer_id'] = $customerId;

        if (self::getOrderStatusId($orderStatus) == -1)
            return "Invalid order status.";
        else
            $params['status'] = self::getOrderStatusId($orderStatus);

        if ($branchId) {
            if (empty(Branches::getBranches(branchId: $branchId)))
                return "Invalid branch Id.";
            $params['branch_id'] = $branchId;
        }

        $itemIds = [];
        foreach ($items as &$singleItem) {
            foreach ($singleItem as $itemId => &$itemData) {
                if (!is_int($itemId))
                    return "All item ids must be integers.";
                if (!isset($itemData['amount'], $itemData['return-date'], $itemData['defects']))
                    return "Every item should contain 'amount', 'return-date', 'defects'";
                if ($itemData['amount'] < 1)
                    return "All amounts must be greater than 0";
                if (!is_array($itemData['defects']))
                    return "Defects should be an array.";
                if (!is_string($itemData['return-date']))
                    return "'return-date' should be a string.";

                $itemData['amount'] = intval($itemData['amount']);
                $itemIds[] = "item_id=$itemId";
            }
        }
        unset($singleItem, $itemData);

        $itemIds = array_unique($itemIds);
        $condition = "(" . implode(' OR ', $itemIds) . ")";
        $condition .= " AND blocked=false";
        $itemDataStored = (self::getDataFromTable(['price', 'item_id'], 'items', $condition))->fetchAll(PDO::FETCH_ASSOC);

        if (count($itemDataStored) < count($itemIds))
            return "Invalid item ids were sent";

        $calculatedTotalPrice = 0;
        $newItemData = [];

        foreach ($items as $singleItem) {
            foreach ($singleItem as $itemId => $itemData) {
                foreach ($itemDataStored as $item) {
                    if ($itemId == $item['item_id']) {
                        $item['amount'] = $itemData['amount'];
                        $item['return-date'] = $itemData['return-date'];
                        $item['defects'] = $itemData['defects'];
                        $calculatedTotalPrice += intval($itemData['amount']) * floatval($item['price']);
                        $newItemData[] = $item;
                    }
                }
            }
        }
        $newItemData = json_encode($newItemData);

        if ($totalPrice)
            $params['total_price'] = $totalPrice;
        else
            $params['total_price'] = $calculatedTotalPrice;
        $params['items'] = $newItemData;
        $params['added_date'] = (new DateTime('now'))->format("Y-m-d H:m");

        if ($comments)
            $params['comments'] = $comments;

        $id = self::insertIntoTable('orders', $params);
        if ($id === false)
            return false;
        else
            return ['order_id' => $id];
    }

    public static function getOrders(int $pageNumber = 0, int $orderId = null, string $addedDate = null,
                                     int $branchId = null, string $orderStatus = null, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($orderId)
            $filters[] = "order_id=$orderId";
        if ($addedDate) {
            $filters[] = "added_date LIKE :date";
            $placeholders['date'] = $addedDate . "%";
        }
        if ($branchId)
            $filters[] = "branch_id=$branchId";
        if ($orderStatus) {
            $orderStatus = self::getOrderStatusId($orderStatus);
            $filters[] = "status=$orderStatus";
        }
        $condition = implode(" AND ", $filters);

        $orders = (self::getDataFromTable(["*"], 'orders', $condition, $placeholders, ['order_id', 'desc'],
            [$startingIndex, $limit]))->fetchAll(PDO::FETCH_ASSOC);


        $itemIds = [];
        $customerIDs = [];
        foreach ($orders as &$order) {
            $customerIDs[] = $order['customer_id'];
            $order['items'] = json_decode($order['items'], true);
            for ($i2 = 0; $i2 < count($order['items']); $i2++) {
                $itemIds[] = $order['items'][$i2]['item_id'];
            }
        }
        unset($order);
        $itemIds = array_unique($itemIds);

        $itemData = self::getItemData($itemIds);
        $customerData = self::getCustomerData($customerIDs);

        foreach ($orders as &$order) {
            $paymentData = Payments::getPayments(orderId: $order['order_id'], limit: 1000);
            foreach ($paymentData as &$paymentDatum) {
                unset($paymentDatum['order_id']);
            }
            $order['payments'] = $paymentData;
            foreach ($order['items'] as &$item) {
                // Adding item name
                foreach ($itemData as $oneItem) {
                    if (isset($item['item_name']))
                        break;
                    if ($oneItem['item_id'] == $item['item_id']) {
                        $item['item_name'] = $oneItem['name'];
                        $item['categories'] = $oneItem['categories'];
                    }
                }
                if (!isset($item['item_name'])) {
                    $item['item_name'] = "DELETED ITEM";
                    $item['item_id'] = null;
                }

                // Adding customer name
                foreach ($customerData as $oneCustomer) {
                    if (isset($order['customer_name']))
                        break;
                    if ($oneCustomer['customer_id'] == $order['customer_id'])
                        $order['customer_name'] = $oneCustomer['name'];
                }
                if (!isset($order['customer_name'])) {
                    $order['customer_name'] = "DELETED CUSTOMER";
                    $order['customer_id'] = null;
                }

                // Adding order status message
                $order['status'] = self::getOrderStatusMessage(intval($order['status']));
            }
        }
        unset($order, $item);
        return $orders;
    }

    public static function updateOrder(int $orderId, int $branchId = null, string $orderStatus = null,
                                       int $customerId = null, array $items = null, float $totalPrice = null): bool|string
    {
        $orderData = self::getOrders(orderId: $orderId)[0];
        if (empty($orderData))
            return "Invalid order id.";

        $params = [];

        if (empty(Customers::getCustomers($customerId)))
            return "Invalid customer-id";
        else
            $params['customer_id'] = $customerId;

        if ($items) {
            $itemIds = [];
            foreach ($items as $singleItem) {
                foreach ($singleItem as $itemId => $itemDataGiven) {
                    if (!is_int($itemId))
                        return "All item ids must be integers.";
                    if (!is_array($itemDataGiven))
                        return "Invalid item content.";
                    $itemIds[] = "item_id=$itemId";
                }
            }
            $itemIds = array_unique($itemIds);
            $condition = "(" . implode(' OR ', $itemIds) . ")";
            $condition .= " AND blocked=false";
            $itemData = (self::getDataFromTable(['price', 'item_id'], 'items', $condition))->fetchAll(PDO::FETCH_ASSOC);

            if (count($itemData) < count($itemIds))
                return "Invalid item ids were sent.";

            $calculatedTotalPrice = 0;
            $newItemData = [];
            foreach ($items as $singleItem) {
                foreach ($singleItem as $itemId => $itemDataGiven) {
                    foreach ($itemData as $item) {
                        if ($itemId == $item['item_id']) {
                            $item['amount'] = $itemDataGiven['amount'];
                            $item['return-date'] = $itemDataGiven['return-date'];
                            $item['defects'] = $itemDataGiven['defects'];
                            $calculatedTotalPrice += intval($itemDataGiven['amount']) * floatval($item['price']);
                            $newItemData[] = $item;
                        }
                    }
                }
            }

            $params['items'] = json_encode($newItemData);
            if ($totalPrice)
                $params['total_price'] = $totalPrice;
            else
                $params['total_price'] = $calculatedTotalPrice;
        }
        if ($totalPrice)
            $params['total_price'] = $totalPrice;
        if ($branchId) {
            if (empty(Branches::getBranches(branchId: $branchId)))
                return "Invalid branch Id.";
            $params['branch_id'] = $branchId;
        }
        if ($orderStatus) {
            if (self::getOrderStatusId($orderStatus) == -1)
                return "Invalid order status.";
            else
                $params['status'] = self::getOrderStatusId($orderStatus);
        }

        $condition = "order_id=$orderId";
        return self::updateTableData('orders', $params, $condition);
    }

    public static function getOrderCount(int $branchId, int $noDaysBackward = 7): array
    {
        $counts = [];
        for ($i = 0; $i < $noDaysBackward + 1; $i++) {
            $date = ((new DateTime('now'))
                ->sub(new DateInterval("P" . $i . "D")))->format("Y-m-d");
            $condition = "branch_id=$branchId AND added_date LIKE '" . $date . "%'";

            $counts[$date] = (self::getDataFromTable(["order_id"], 'orders', $condition))->rowCount();
        }
        return $counts;
    }

    public static function getStatusMessages(): array
    {
        return ["order added", "payment pending", "payment completed", "processing order", "finished processing order",
            "order completed", "order on hold", "order rejected", "order cancelled"];
    }

    public static function deleteOrder(int $orderId): bool
    {
        $sql = "DELETE FROM orders WHERE order_id=$orderId";
        if (self::exec($sql))
            return true;
        return false;
    }

    private static function getOrderStatusMessage(int $statusId): string
    {
        if ($statusId == self::STATUS_ORDER_ADDED)
            return "order added";
        elseif ($statusId == self::STATUS_PENDING_PAYMENT)
            return "payment pending";
        elseif ($statusId == self::STATUS_PAYMENT_COMPLETED)
            return "payment completed";
        elseif ($statusId == self::STATUS_PROCESSING)
            return "processing order";
        elseif ($statusId == self::STATUS_COMPLETED_PROCESSING)
            return "finished processing order";
        elseif ($statusId == self::STATUS_COMPLETED)
            return "order completed";
        elseif ($statusId == self::STATUS_ON_HOLD)
            return "order on hold";
        elseif ($statusId == self::STATUS_REJECTED)
            return "order rejected";
        elseif ($statusId == self::STATUS_CANCELLED)
            return "order cancelled";
        return "unknown order status";
    }

    private static function getOrderStatusId(string $statusMessage): int
    {
        if ($statusMessage == "order added")
            return self::STATUS_ORDER_ADDED;
        elseif ($statusMessage == "payment pending")
            return self::STATUS_PENDING_PAYMENT;
        elseif ($statusMessage == "payment completed")
            return self::STATUS_PAYMENT_COMPLETED;
        elseif ($statusMessage == "processing order")
            return self::STATUS_PROCESSING;
        elseif ($statusMessage == "finished processing order")
            return self::STATUS_COMPLETED_PROCESSING;
        elseif ($statusMessage == "order completed")
            return self::STATUS_COMPLETED;
        elseif ($statusMessage == "order on hold")
            return self::STATUS_ON_HOLD;
        elseif ($statusMessage == "order rejected")
            return self::STATUS_REJECTED;
        elseif ($statusMessage == "order cancelled")
            return self::STATUS_CANCELLED;
        return -1;
    }

    private static function getItemData(array $itemIds): array
    {
        $filters = [];
        foreach ($itemIds as $itemId)
            $filters[] = "item_id=$itemId";
        $condition = implode(" OR ", $filters);
        $itemData = (self::getDataFromTable(['item_id', 'name', 'price', 'category_ids'], 'items', $condition))
            ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($itemData as &$item) {
            $categoryIds = explode(',', $item['category_ids']);
            $categoryData = PriceCategories::getCategories(limit: 1000, categoryIds: $categoryIds);
            $item['categories'] = $categoryData;
        }
        return $itemData;
    }

    private static function getCustomerData(array $customerIds): array
    {
        $filters = [];
        foreach ($customerIds as $customerId)
            $filters[] = "customer_id=$customerId";
        $condition = implode(" OR ", $filters);
        return (self::getDataFromTable(['customer_id', 'name'], 'customers', $condition))->fetchAll(PDO::FETCH_ASSOC);
    }
}