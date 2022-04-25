<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Pay\Observer;

class UpdateBillingAddress extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Pay\Helper\Data
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $paymentMethod = $observer->getEvent()->getPaymentMethod();
        $helper = $this->payHelper;
        /* @var $helper \Epicor\Pay\Helper\Data */
        $helper->setQuoteDefaultBillingAddress($paymentMethod);
    }

}