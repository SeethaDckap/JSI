<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Order\Payment\Operations;

use Epicor\Customerconnect\Api\Data\OrderPaymentInterface;
use Epicor\Customerconnect\Model\ArPayment\Order\Invoice;
use Epicor\Customerconnect\Model\ArPayment\Order\Payment;
use Epicor\Customerconnect\Model\ArPayment\Order\Payment\Transaction;

class RegisterCaptureNotificationOperation extends AbstractOperation
{
    /**
     * Registers capture notification.
     *
     * @param OrderPaymentInterface $payment
     * @param string|float $amount
     * @param bool|int $skipFraudDetection
     * @return OrderPaymentInterface
     */
    public function registerCaptureNotification(OrderPaymentInterface $payment, $amount, $skipFraudDetection = false)
    {
        /**
         * @var $payment Payment
         */
        $payment->setTransactionId(
            $this->transactionManager->generateTransactionId(
                $payment,
                Transaction::TYPE_CAPTURE,
                $payment->getAuthorizationTransaction()
            )
        );

        $order = $payment->getOrder();
        $amount = (double)$amount;
        $invoice = $this->getInvoiceForTransactionId($order, $payment->getTransactionId());

        // register new capture
        if (!$invoice) {
            if ($payment->isSameCurrency() && $payment->isCaptureFinal($amount)) {
                $invoice = $order->prepareInvoice()->register();
                $invoice->setOrder($order);
                $order->addRelatedObject($invoice);
                $payment->setCreatedInvoice($invoice);
                $payment->setShouldCloseParentTransaction(true);
            } else {
                $payment->setIsFraudDetected(!$skipFraudDetection);
                $this->updateTotals($payment, ['base_amount_paid_online' => $amount]);
            }
        }

        if (!$payment->getIsTransactionPending()) {
            if ($invoice && Invoice::STATE_OPEN == $invoice->getState()) {
                $invoice->setOrder($order);
                $invoice->pay();
                $this->updateTotals($payment, ['base_amount_paid_online' => $amount]);
                $order->addRelatedObject($invoice);
            }
        }

        $message = $this->stateCommand->execute($payment, $amount, $order);
        $transaction = $payment->addTransaction(
            Transaction::TYPE_CAPTURE,
            $invoice,
            true
        );
        $message = $payment->prependMessage($message);
        $payment->addTransactionCommentsToOrder($transaction, $message);
        return $payment;
    }
}
