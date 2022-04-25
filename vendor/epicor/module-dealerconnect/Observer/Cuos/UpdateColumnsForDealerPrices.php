<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Observer\Cuos;

class UpdateColumnsForDealerPrices extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $isDealer = $this->customerSession->getCustomer()->isDealer();
        $columns = $observer->getEvent()->getColumns();
        /* @var $columns Varien_Object */
        $originalValue = $columns->getOriginalValue();
        $dealerPrice = $columns->getDealerGrandTotalInc();
        if ($isDealer && $this->dealerHelper->isDealerPortal()) {
           $currentMode = $this->customerSession->getDealerCurrentMode();
           $dealerPrice['column_css_class'] = $currentMode === "dealer" ? "no-display" : "";
           $dealerPrice['header_css_class'] = $currentMode === "dealer" ? "no-display" : "";
           $originalValue['column_css_class'] = $currentMode === "shopper" ? "no-display" : "";
           $originalValue['header_css_class'] = $currentMode === "shopper" ? "no-display" : "";
        }else{
            $dealerPrice['column_css_class'] = "no-display" ;
            $dealerPrice['header_css_class'] = "no-display" ;
        }
            $columns->setOriginalValue($originalValue);
            $columns->setDealerGrandTotalInc($dealerPrice);
    }

}
