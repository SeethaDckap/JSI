<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\Order\Payment\Transaction;

use Epicor\Customerconnect\Api\Data\OrderPaymentInterface;
use Epicor\Customerconnect\Model\ArPayment\Order\Payment\Transaction;

/**
 * Manage payment transaction
 */
interface ManagerInterface
{
    /**
     * Lookup an authorization transaction using parent transaction id, if set
     *
     * @param string $parentTransactionId
     * @param int $paymentId
     * @param int $orderId
     * @return false|Transaction
     */
    public function getAuthorizationTransaction($parentTransactionId, $paymentId, $orderId);

    /**
     * Checks if transaction exists by txt id
     *
     * @param string $transactionId
     * @param int $paymentId
     * @param int $orderId
     * @return bool
     */
    public function isTransactionExists($transactionId, $paymentId, $orderId);

    /**
     * Update transaction ids for further processing
     * If no transactions were set before invoking, may generate an "offline" transaction id
     *
     * @param OrderPaymentInterface $payment
     * @param string $type
     * @param bool|Transaction $transactionBasedOn
     * @return string|null
     */
    public function generateTransactionId(OrderPaymentInterface $payment, $type, $transactionBasedOn = false);
}
