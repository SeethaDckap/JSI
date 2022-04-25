<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SaveQuoteAddress extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Triggered when the base url changes, sends a SYN message with the provided new url
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuoteItem()->getQuote();
        // check if quote has a shipping address . if not use customers default shipping address 
        $shippingAddress = $quote->getShippingAddress()->getStreet();

        if (!$shippingAddress[0]) {
            $customerAddressId = $this->customerSessionFactory->create()->getCustomer()->getDefaultShipping();
            if ($customerAddressId) {
                $shipToAddress = $this->customerAddressFactory->create()->load($customerAddressId);
                $quoteShippingAddress = $this->quoteQuoteAddressFactory->create();
                $quoteShippingAddress->setData($shipToAddress->getData());
                $quoteShippingAddress->setCustomerAddressId($shipToAddress->getId());
                $quote->setShippingAddress($quoteShippingAddress);
                $quote->getShippingAddress()->setCustomerAddressId($shipToAddress->getId());
                $quote->getShippingAddress()->setEccErpAddressCode($shipToAddress->getEccErpAddressCode());
            }
        }
    }

}