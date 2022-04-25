<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Observer;

//class CheckForEmptyCart extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
class CheckForEmptyCart extends AbstractObserver
{
    /**
     * @var \Epicor\Quotes\Helper\Data
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isMassListAdd = $observer->getEvent()->getCart()->getData('is_mass_list_add');
        $quote = $observer->getEvent()->getCart()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        if ($quote->getItemsSummaryQty() == 0 && $quote->hasEccQuoteId()) {
            $quote->setEccQuoteId(null);
            $quote->setEccErpQuoteId(null);
        }

        if ($quote->getItemsSummaryQty() == 0) {
            $quote->setEccIsDdaDate(false);
            $quote->setEccRequiredDate("0000-00-00");
            $quote->setEccBsvGoodsTotal(null);
            $quote->setEccBsvGoodsTotalInc(null);
            $quote->setEccBsvCarriageAmount(null);
            $quote->setEccBsvCarriageAmountInc(null);
            $quote->setEccBsvGrandTotal(null);
            $quote->setEccBsvGrandTotalInc(null);
        }

        if($quote->getId() && !$isMassListAdd) {
            $allowSaving = $quote->getAllowSaving();
            $quote->load($quote->getId());
            $quote->setAllowSaving($allowSaving);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();
        }
        $observer->getEvent()->getCart()->setQuote($quote);
        return $observer;
    }

}