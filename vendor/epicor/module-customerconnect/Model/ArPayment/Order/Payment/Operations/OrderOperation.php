<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Order\Payment\Operations;

use Epicor\Customerconnect\Api\Data\OrderPaymentInterface;
use Epicor\Customerconnect\Model\ArPayment\Order\Payment;
use Epicor\Customerconnect\Model\ArPayment\Order\Payment\Transaction;

/**
 * Class Order
 */
class OrderOperation extends AbstractOperation
{
    /**
     * @param OrderPaymentInterface $payment
     * @param string|float $amount
     * @return OrderPaymentInterface
     */
    public function order(OrderPaymentInterface $payment, $amount)
    {
        /**
         * @var $payment Payment
         */
        // update totals
        $amount = $payment->formatAmount($amount, true);

        // do ordering
        $order = $payment->getOrder();

        $method = $payment->getMethodInstance();
        $method->setStore($order->getStoreId());
        $method->order($payment, $amount);

        if ($payment->getSkipOrderProcessing()) {
            return $payment;
        }

        $message = $this->stateCommand->execute($payment, $amount, $order);
        // update transactions, order state and add comments
        $transaction = $payment->addTransaction(Transaction::TYPE_ORDER);
        $message = $payment->prependMessage($message);
        $payment->addTransactionCommentsToOrder($transaction, $message);

        return $payment;
    }
}
