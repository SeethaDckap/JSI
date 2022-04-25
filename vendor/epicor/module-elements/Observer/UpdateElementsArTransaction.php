<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elements\Observer;

use Epicor\Elements\Model\UpdateTransactionData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class UpdateElementsArTransaction implements ObserverInterface
{
    /**
     * Path for Authorize in ECC configuration
     */
    const XML_PAYMENT_ELEMENTS_AUTHINECC = 'payment/elements/authorizeInEcc';

    /**
     * @var UpdateTransactionData
     */
    private $updateTransactionData;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * UpdateElementsArTransaction constructor.
     * @param UpdateTransactionData $updateTransactionData
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UpdateTransactionData $updateTransactionData,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->updateTransactionData = $updateTransactionData;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $authorizeInEcc = $this->scopeConfig->getValue(
            self::XML_PAYMENT_ELEMENTS_AUTHINECC,
            ScopeInterface::SCOPE_STORE
        );

        if (($payment->getMethod() == 'elements') && (!$authorizeInEcc)) {
            $transactionId = $payment->getLastTransid();
            $orderId = $order->getIncrementId();

            $this->updateTransactionData->updateElementsTransaction($transactionId, $orderId);
        }
    }
}
