<?php

namespace LogicLeap\StockManagement\models\accounting;

use LogicLeap\StockManagement\models\DbModel;
use PDO;

class Accounting extends DbModel
{
    private const TABLE_NAME = 'financial_accounts';

    private const ACCOUNT_TYPES = [
        "assets" => ['Current Asset', 'Fixed Asset', 'Inventory', 'Non-Current Asset', 'Prepayment'],
        "equity" => ['Equity'],
        "expenses" => ['Depreciation', 'Direct Costs', 'Expense', 'Overhead'],
        "liabilities" => ['Current Liability', 'Liability', 'Non-Current Liability'],
        "revenue" => ['Other Income', 'Revenue', 'Sales']
    ];

    public static function getAccounts(int    $pageNumber = 0, int $accountId = null, string $name = null, string $code = null,
                                       string $type = null, string $description = null, int $taxId = null, int $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];

        if ($accountId)
            $filters[] = "account_id=$accountId";
        if ($name) {
            $filters[] = "name LIKE :name";
            $placeholders['name'] = ucwords($name) . "%";
        }
        if ($type) {
            $filters[] = 'type LIKE :type';
            $placeholders['type'] = "$type%";
        }
        if ($code) {
            $filters[] = 'code LIKE :code';
            $placeholders['code'] = strtoupper($code). "%";
        }
        if ($description) {
            $filters[] = 'description LIKE :desc';
            $placeholders['desc'] = "$description%";
        }
        if ($taxId) {
            $filters[] = "tax_id = $taxId";
        }

        $condition = implode(' AND ', $filters);
        $statement = self::getDataFromTable([" * "], self::TABLE_NAME, $condition, $placeholders,
            ['account_id', 'asc'], [$startingIndex, $limit]);
        $accounts = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($accounts as &$account) {
            $account['archived'] = boolval($account['archived']);
            $account['deletable'] = boolval($account['deletable']);
        }
        return $accounts;
    }

    public static function createAccount(string $name, string $code, string $type, int $taxId, string $description = null,
                                         bool   $deletable = true): string|array
    {
        $params['name'] = ucwords($name);
        $params['code'] = strtoupper($code);

        if (strlen($code) > 10)
            return "Account code can not be more than 10 characters . ";

        $statement = self::getDataFromTable(['name', 'code'], self::TABLE_NAME,
            'name=:name OR code=:code', $params);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($data))
            if ($data['name'] == $name && $data['code'] == $code)
                return "Account 'name' and 'code' already exists . ";
            elseif ($data['name'] == $name)
                return "Account 'name' already exists . ";
            else
                return "Account 'code' already exists . ";

        foreach (self::ACCOUNT_TYPES as $mainType => $subTypes) {
            foreach ($subTypes as $subType) {
                if ($subType === ucwords($type)) {
                    $params['type'] = $subType;
                    break;
                }
            }
        }
        if (!isset($params['type']))
            return "Invalid account type . ";

        $data = Taxes::getTaxes(taxId: $taxId);
        if (empty($data))
            return "Invalid tax ID . ";

        $params['tax_id'] = $taxId;
        $params['description'] = $description;
        $params['archived'] = false;
        $params['deletable'] = $deletable;

        $id = self::insertIntoTable(self::TABLE_NAME, $params);
        if ($id === false)
            return "Failed to insert data into the database . ";

        return ['account_id' => $id];
    }

    public static function updateAccount(int $accountId, string $name = null, int $taxId = null, string $description = null): string|bool
    {
        $data = self::getDataFromTable(['account_id'], self::TABLE_NAME,
            "account_id = $accountId")->fetch(PDO::FETCH_ASSOC);
        if (empty($data))
            return "Invalid account ID . ";

        if (empty($name) && empty($taxId) && empty($description))
            return "Nothing was passed to update . ";

        if ($name) {
            $params['name'] = ucwords($name);
            // $params['id'] = $accountId;
            $data = self::getDataFromTable(['account_id'], self::TABLE_NAME,
                'name=:name AND account_id <> ' . $accountId, $params)->fetch(PDO::FETCH_ASSOC); // Please change this, I can't figure out how to bind the value
            if (!empty($data))
                return "Account name already in use.";
        }
        if ($taxId) {
            $data = Taxes::getTaxes(taxId: $taxId);
            if (empty($data))
                return "Invalid tax ID . ";
            $params['tax_id'] = $taxId;
        }
        if ($description)
            $params['description'] = $description;

        if (self::updateTableData(self::TABLE_NAME, $params, "account_id = $accountId"))
            return true;
        return "Failed to update the database . ";
    }

    public static function deleteAccount(int $accountId): bool|string
    {
        $data = self::getDataFromTable(['deletable'], self::TABLE_NAME,
            "account_id = $accountId")->fetch(PDO::FETCH_ASSOC);
        if (empty($data))
            return "Invalid account ID . ";

        if (!$data['deletable'])
            return "This account can not be deleted . ";

        //Todo implement proper checks
//        $ledgerRecords = GeneralLedger::getLedgerRecords(accountId: $accountId);
        if (!empty($ledgerRecords)) {
            self::updateTableData(self::TABLE_NAME, ['deletable' => false], "account_id = $accountId");
            return "This account can not be deleted . ";
        }

        if (self::removeTableData(self::TABLE_NAME, "account_id = $accountId"))
            return true;
        return "Failed to remove data from database . ";
    }

    public static function getAccountTypes(): array
    {
        return self::ACCOUNT_TYPES;
    }
}