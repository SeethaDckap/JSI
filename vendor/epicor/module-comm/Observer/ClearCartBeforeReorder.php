<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ClearCartBeforeReorder extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    const CART_MERGE_ACTION = 'cart_merge_action';
    const CLEAR = 'clear';
    /**
     * Triggered when the reorder button is clicked
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cartMergeAction = $this->scopeConfig->getValue(self::CART_MERGE_ACTION, $this->storeManager->getStore()->getStoreId());

        //only clear the cart in a reorder if the cart merge action is set to do so
        if ($cartMergeAction == self::CLEAR) {
            $quote = $this->checkoutSession->getQuote();
            /* @var $quote Mage_Sales_Model_Quote */

            $quote->removeAllItems();
            $quote->setEccQuoteId(null);
            $quote->setEccIsDdaDate(false);
            $quote->setAllowSaving(true);
            $quote->save();
        }
    }

}