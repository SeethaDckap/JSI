<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Observer;

use Epicor\Elements\Model\UpdateTransactionData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UpdateElementsTransaction implements ObserverInterface
{
    /**
     * @var UpdateTransactionData
     */
    private $updateTransactionData;

    /**
     * UpdateElementsTransaction constructor.
     * @param UpdateTransactionData $updateTransactionData
     */
    public function __construct(
        UpdateTransactionData $updateTransactionData
    ) {
        $this->updateTransactionData = $updateTransactionData;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if (($payment->getMethod() == 'elements') && ($order->getArpaymentsQuote() != '1')) {
            $transactionId = $payment->getLastTransid();
            $orderId = $order->getIncrementId();

            $this->updateTransactionData->updateElementsTransaction($transactionId, $orderId);
        }
    }
}
