<?php

namespace LogicLeap\StockManagement\controllers;

use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\stock_management\Orders;
use LogicLeap\StockManagement\models\user_management\User;

class OrderController extends API
{
    public function getOrders(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $orderId = self::getParameter('order-id', dataType: 'int');
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $addedDate = self::getParameter('added-date');
        $orderStatus = self::getParameter('order-status');

        $orders = Orders::getOrders($pageNumber, $orderId, $addedDate, $branchId, $orderStatus);
        self::sendSuccess(['orders' => $orders]);
    }

    public function addOrder(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_WRITE]]);

        $items = self::getParameter('items', dataType: 'array', isCompulsory: true);
        $totalPrice = self::getParameter('total-price', dataType: 'float');
        $customerId = self::getParameter('customer-id', dataType: 'int', isCompulsory: true);
        $comments = self::getParameter('customer-comments');

        $branchId = User::getUserBranchId(self::getUserId());
        if ($branchId == 0)
            $branchId = self::getParameter('branch-id', dataType: 'int');

        $status = Orders::addNewOrder($items, $customerId, $totalPrice, $branchId, $comments);
        if (is_array($status))
            self::sendSuccess(['message' => 'New order added successfully.', 'order-id' => $status['order_id']]);
        elseif ($status === false)
            self::sendError("Failed to add new order.");
        else
            self::sendError($status);
    }

    public function updateOrder(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_MODIFY]]);

        $orderId = self::getParameter('order-id', dataType: 'int', isCompulsory: true);
        $branchId = self::getParameter('branch-id', dataType: 'int');
        $customerId = self::getParameter('customer-id', dataType: 'int');
        $status = self::getParameter('order-status');
        $items = self::getParameter('items', dataType: 'array');
        $totalPrice = self::getParameter('total-price', dataType: 'float');

        $status = Orders::updateOrder($orderId, $branchId, $status, $customerId, $items, $totalPrice);
        if ($status === true)
            self::sendSuccess("Order updated successfully.");
        elseif ($status === false)
            self::sendError("Failed to update the order.");
        else
            self::sendError($status);
    }

    public function deleteOrder(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_DELETE]]);

        $orderId = self::getParameter('order-id', dataType: 'int', isCompulsory: true);

        if (Orders::deleteOrder($orderId))
            self::sendSuccess("Order deleted successfully.");
        else
            self::sendError('Failed to delete the order.');
    }

    public function getOrderCount(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_READ]]);

        $branchId = User::getUserBranchId(self::getUserId());
        if ($branchId == 0)
            $branchId = self::getParameter('branch-id', dataType: 'int', isCompulsory: true);
        $noOfDaysBackward = self::getParameter('no-days-backward', defaultValue: 7, dataType: 'int');

        self::sendSuccess(['order-counts' => Orders::getOrderCount($branchId, $noOfDaysBackward)]);
    }

    public function getOrderStatusMessages(): void
    {
        self::checkPermissions(['orders' => [User::PERMISSION_READ]]);

        self::sendSuccess(['status-messages' => Orders::getStatusMessages()]);
    }
}
