<?php

namespace LogicLeap\StockManagement\controllers\v1\stock_management;

use LogicLeap\StockManagement\models\API;
use LogicLeap\StockManagement\models\stock_management\PriceCategories;
use LogicLeap\StockManagement\models\user_management\User;

class PriceCategoryController extends API
{
    public function getPriceCategories(): void
    {
        self::checkPermissions(['categories' => [User::PERMISSION_READ]]);

        $pageNum = self::getParameter('page-num', 0, 'int');
        $categoryName = self::getParameter('category-name');

        $data = PriceCategories::getCategories($pageNum, $categoryName);
        self::sendSuccess(['categories' => $data]);
    }

    public function addPriceCategory(): void
    {
        self::checkPermissions(['categories' => [User::PERMISSION_WRITE]]);

        $categoryName = self::getParameter('category-name', isCompulsory: true);

        $status = PriceCategories::addCategory($categoryName);
        if (is_array($status))
            self::sendSuccess(['message' => 'New category was created successfully.', 'category-id' => $status['category_id']]);
        elseif ($status === false)
            self::sendError('Failed to add new category.');
        else
            self::sendError($status);
    }

    public function updatePriceCategory(): void
    {
        self::checkPermissions(['categories' => [User::PERMISSION_MODIFY]]);

        $categoryId = self::getParameter('category-id', dataType: 'int', isCompulsory: true);
        $categoryName = self::getParameter('category-name', isCompulsory: true);

        if (PriceCategories::updateCategory($categoryId, $categoryName))
            self::sendSuccess('Category was updated successfully.');
        else
            self::sendError('Failed to update the category.');
    }

    public function deletePriceCategory(): void
    {
        self::checkPermissions(['categories' => [User::PERMISSION_DELETE]]);

        $categoryId = self::getParameter('category-id', dataType: 'int', isCompulsory: true);

        if (PriceCategories::deleteCategory($categoryId))
            self::sendSuccess('Category was deleted successfully.');
        else
            self::sendError('Failed to delete the category.');
    }
}
