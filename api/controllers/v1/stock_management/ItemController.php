<?php

namespace LogicLeap\StockManagement\controllers\v1\stock_management;

use LogicLeap\PhpServerCore\Controller;
use LogicLeap\StockManagement\models\stock_management\Items;
use LogicLeap\StockManagement\models\user_management\User;

class ItemController extends Controller
{   
    public function getItems(): void
    {
        self::checkPermissions(['products' => [User::PERMISSION_READ]]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $itemName = self::getParameter('item-name');
        $itemId = self::getParameter('item-id', dataType: 'int');
        $price = self::getParameter('item-price', dataType: 'float');
        $blocked = self::getParameter('blocked', dataType: 'bool');

        $data = Items::getItems($pageNum, $itemId, $itemName, $price, $blocked);
        self::sendSuccess(['items' => $data]);
    }

    public function addItem(): void
    {
        self::checkPermissions(['products' => [User::PERMISSION_WRITE]]);

        $itemName = self::getParameter('item-name', isCompulsory: true);
        $prices = self::getParameter('item-price', defaultValue: [], dataType: 'array', isCompulsory: true);
        $blocked = self::getParameter('blocked', defaultValue: false, dataType: 'bool');

        $status = Items::addItem($itemName, $prices, $blocked);
        if (is_array($status))
            self::sendSuccess(['message' => 'New item was added successfully.', 'item-id' => $status['item_id']]);
        elseif ($status === false)
            self::sendError('Failed to add new item.');
        else
            self::sendError($status);
    }

    public function updateItem(): void
    {
        self::checkPermissions(['products' => [User::PERMISSION_MODIFY]]);

        $itemId = self::getParameter('item-id', dataType: 'int', isCompulsory: true);
        $itemName = self::getParameter('item-name');
        $prices = self::getParameter('item-price', dataType: 'array');
        $blocked = self::getParameter('blocked', dataType: 'bool');

        $status = Items::updateItem($itemId, $itemName, $prices, $blocked);
        if ($status === true)
            self::sendSuccess('Item was updated successfully.');
        elseif ($status === false)
            self::sendError('Failed to update the item.');
        else
            self::sendError($status);
    }

    public function deleteItem(): void
    {
        self::checkPermissions(['products' => [User::PERMISSION_DELETE]]);

        $itemId = self::getParameter('item-id', dataType: 'int', isCompulsory: true);

        if (Items::deleteItem($itemId))
            self::sendSuccess('Item was deleted successfully.');
        else
            self::sendError('Failed to delete the item.');
    }
}