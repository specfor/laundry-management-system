<?php

namespace LogicLeap\StockManagement\models\stock_management;

use DateTime;
use LogicLeap\StockManagement\models\DbModel;
use PDO;

class Payments extends DbModel
{
    public static function addPayment(int $orderId, float $paidAmount, string $paidDate = null): bool|string|array
    {
        $paymentsToOrderData = (self::getDataFromTable(['payment_id', 'paid_amount'], 'payments',
            "order_id=$orderId AND refunded=false"))->fetchAll(PDO::FETCH_ASSOC);
        $orderData = (self::getDataFromTable(['total_price'], 'orders',
            "order_id=$orderId"))->fetch(PDO::FETCH_ASSOC);

        if (empty($orderData))
            return "Invalid order id.";

        $totalPaidAmount = 0;
        if (!empty($paymentsToOrderData)) {
            foreach ($paymentsToOrderData as $payment) {
                $totalPaidAmount += $payment['paid_amount'];
            }
            if ($totalPaidAmount == $orderData['total_price'])
                return "Order is already paid.";
        }

        if (($orderData['total_price'] - $totalPaidAmount) < $paidAmount)
            if ($totalPaidAmount == 0)
                return "Total price of the order is only " . $orderData['total_price'] . ".";
            else
                return "Total amount left to pay is " . ($orderData['total_price'] - $totalPaidAmount) . ".";

        $params['order_id'] = $orderId;
        $params['paid_amount'] = $paidAmount;
        if ($paidDate)
            $params['paid_date'] = $paidDate;
        else {
            $params['paid_date'] = (new DateTime('now'))->format("Y-m-d");
        }

        $id = self::insertIntoTable('payments', $params);
        if ($id === false)
            return false;
        else
            return ['payment_id' => $id];
    }

    public static function getPayments(int    $pageNumber = 0, int $paymentId = null, int $orderId = null,
                                       string $paidDate = null, $limit = 30): array
    {
        $startingIndex = $pageNumber * $limit;
        $filters = [];
        $placeholders = [];
        if ($paymentId)
            $filters[] = "payment_id=$paymentId";
        if ($orderId)
            $filters[] = "order_id=$orderId";
        if ($paidDate) {
            $filters[] = "paid_date=:paid_date";
            $placeholders['paid_date'] = $paidDate;
        }
        $condition = implode(' AND ', $filters);
        $data = (self::getDataFromTable(['*'], 'payments', $condition, $placeholders, ['payment_id', 'desc'],
            [$startingIndex, $limit]))->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['refunded'] = boolval($data[$i]['refunded']);
        }
        $count = self::countTableRows('payments', $condition, $placeholders);
        return ["payments" => $data, 'record_count' => $count];
    }

    public static function updatePayment(int $paymentId, bool $refunded): bool
    {
        $params['refunded'] = $refunded;
        return self::updateTableData('payments', $params, "payment_id=$paymentId");
    }

    public static function deletePayment(int $paymentId): bool
    {
        $sql = "DELETE FROM payments WHERE payment_id=$paymentId";
        if (self::exec($sql))
            return true;
        return false;
    }
}