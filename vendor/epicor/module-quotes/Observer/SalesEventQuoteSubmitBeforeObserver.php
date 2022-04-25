<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesEventQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * Set ecc_quote_id 7 ecc_erp_quote_id  to order from quote 
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$observer->getEvent()->getOrder()->setGiftMessageId($observer->getEvent()->getQuote()->getGiftMessageId());
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $order->setEccQuoteId($quote->getEccQuoteId());
        $order->setEccErpQuoteId($quote->getEccErpQuoteId());
        if (!is_null($quote->getShippingAddress()->getEccBsvGoodsTotalInc())) {
          $order->setBaseSubtotalInclTax($quote->getShippingAddress()->getEccBsvGoodsTotalInc());  
        }           
        return $this;
    }
}
