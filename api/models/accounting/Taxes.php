<?php

namespace LogicLeap\StockManagement\models\accounting;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class Taxes extends DbModel
{
    private const TABLE_NAME = 'taxes';

    public static function getTaxes(int    $pageNumber = 0, int $taxId = null, string $taxName = null,
                                    string $description = null, float $rateMin = null, float $rateMax = null,
                                    int    $limit = 30, bool $deleted = false): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];

        if ($taxId)
            $filters[] = "tax_id=$taxId";
        if ($taxName) {
            $taxName = ucwords($taxName);
            $filters[] = "name LIKE :name";
            $placeholders['name'] = "%$taxName%";
        }
        if ($description) {
            $filters[] = "description LIKE :desc";
            $placeholders['desc'] = "%$description%";
        }
        if ($rateMin)
            $filters[] = "tax_rate > $rateMin";
        if ($rateMax)
            $filters[] = "tax_rate < $rateMax";

        $filters[] = "deleted=:deleted";
        $placeholders['deleted'] = $deleted;

        $condition = implode(' AND ', $filters);

        $statement = self::getDataFromTable(['tax_id', 'name', 'description', 'tax_rate', 'locked'],
            self::TABLE_NAME, $condition, $placeholders, ['tax_id', 'asc'], [$startingIndex, $limit]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createTax(string $taxName, float $rate, string $description = null, bool $locked = false): string|array
    {
        $data = self::getTaxes(taxName: ucwords($taxName));
        if (!empty($data))
            return "Tax name has already been used.";

        $params['name'] = ucwords($taxName);
        $params['description'] = $description;
        $params['tax_rate'] = $rate;
        $params['deleted'] = false;
        $params['locked'] = $locked;

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return 'Failed to insert data into the database.';
        else
            return ['tax_id' => $id];
    }

    public static function updateTax(int $id, string $taxName = null, string $description = null, float $rate = null): string|bool
    {
        $data = self::getTaxes(taxId: $id);
        if (empty($data))
            return "Invalid tax id.";

        if ($data[0]['locked'])
            return "This tax can not be modified.";

        if (empty($taxName) && empty($description) && empty($rate))
            return "'Tax name', 'description' and 'rate' all can not be empty.";

        if ($taxName) {
            $data = self::getTaxes(taxName: $taxName);
            if (!empty($data))
                return "Tax name has already been used.";
        }

        if ($taxName)
            $params['name'] = $taxName;
        if ($description)
            $params['description'] = $description;
        if ($rate)
            $params['tax_rate'] = $rate;

        return self::updateTableData(self::TABLE_NAME, $params, "tax_id=$id");
    }

    public static function deleteTax(int $taxId): bool|string
    {
        $data = self::getTaxes(taxId: $taxId);
        if (empty($data))
            return "Invalid tax id.";

        if ($data[0]['locked'])
            return "This tax can not be removed.";

        return self::updateTableData(self::TABLE_NAME, ['deleted' => true], "tax_id=$taxId");
    }
}