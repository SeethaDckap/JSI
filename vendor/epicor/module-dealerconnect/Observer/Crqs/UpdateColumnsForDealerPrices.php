<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Observer\Crqs;

class UpdateColumnsForDealerPrices extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $isDealer = $this->customerSession->getCustomer()->isDealer();
        
        if ($isDealer && $this->dealerHelper->isDealerPortal()) {
            $columns = $observer->getEvent()->getColumns();
            /* @var $columns Varien_Object */
            $currentMode = $this->customerSession->getDealerCurrentMode();
            $originalValue = $columns->getOriginalValue();
            $dealerPrice = $columns->getDealerGrandTotalInc();
            $dealerPrice['column_css_class'] = $currentMode === "dealer" ? "no-display" : "";
            $dealerPrice['header_css_class'] = $currentMode === "dealer" ? "no-display" : "";
            $originalValue['column_css_class'] = $currentMode === "shopper" ? "no-display" : "";
            $originalValue['header_css_class'] = $currentMode === "shopper" ? "no-display" : "";

            $columns->setOriginalValue($originalValue);
            $columns->setDealerGrandTotalInc($dealerPrice);
        }
    }

}
