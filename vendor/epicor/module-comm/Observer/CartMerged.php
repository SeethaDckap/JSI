<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class CartMerged extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setEccBasketErpQuoteNumber(null);
        /* @var $quote Epicor_Comm_Model_Quote */

        if ($quote->getEccQuoteId()) {
            $quote->setEccQuoteId(null);
            $quote->removeAllItems();
        }

        $this->registry->register('cart_merged', true);

        return $this;
    }

}