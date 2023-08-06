<?php

namespace LogicLeap\StockManagement\controllers\v1\stock_management;

use LogicLeap\StockManagement\controllers\v1\Controller;
use LogicLeap\StockManagement\models\stock_management\Payments;
use LogicLeap\StockManagement\models\user_management\User;

class PaymentController extends Controller
{   
    public function getPayments(): void
    {
        self::checkPermissions(['payments' => [User::PERMISSION_READ]]);

        $pageNumber = self::getParameter('page-num', defaultValue: 0, dataType: 'int');
        $orderId = self::getParameter('order-id', dataType: 'int');
        $paymentId = self::getParameter('payment-id', dataType: 'int');
        $paidDate = self::getParameter('paid-date');

        self::sendSuccess(["payments" => Payments::getPayments($pageNumber, $paymentId, $orderId, $paidDate)]);
    }

    public function addPayment(): void
    {
        self::checkPermissions(['payments' => [User::PERMISSION_WRITE]]);

        $orderId = self::getParameter('order-id', dataType: 'int', isCompulsory: true);
        $paidAmount = self::getParameter('paid-amount', dataType: 'float', isCompulsory: true);
        $paidDate = self::getParameter('paid-date');

        $status = Payments::addPayment($orderId, $paidAmount, $paidDate);
        if (is_array($status))
            self::sendSuccess(['message' => 'Payment added successfully.', 'payment-id' => $status['payment_id']]);
        elseif ($status === false)
            self::sendError('Failed to add payment.');
        else
            self::sendError($status);
    }

    public function updatePayment(): void
    {
        self::checkPermissions(['payments' => [User::PERMISSION_MODIFY]]);

        $paymentId = self::getParameter('payment-id', dataType: 'int', isCompulsory: true);
        $refunded = self::getParameter('refunded', dataType: 'bool', isCompulsory: true);

        if (Payments::updatePayment($paymentId, $refunded))
            self::sendSuccess("Updated the payment details.");
        else
            self::sendError("Failed to update the payment details.");
    }

    public function deletePayment(): void
    {
        self::checkPermissions(['payments' => [User::PERMISSION_DELETE]]);

        $paymentId = self::getParameter('payment-id', dataType: 'int', isCompulsory: true);

        if (Payments::deletePayment($paymentId))
            self::sendSuccess("Deleted the payment successfully.");
        else
            self::sendError("Failed to delete the payment.");
    }
}