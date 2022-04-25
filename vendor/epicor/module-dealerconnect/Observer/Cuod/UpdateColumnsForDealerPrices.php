<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Observer\Cuod;

class UpdateColumnsForDealerPrices extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_orders_misc';
    const FRONTEND_RESOURCE_INFORMATION_READ_INVOICE = 'Epicor_Customerconnect::customerconnect_account_invoices_misc';
    const FRONTEND_RESOURCE_INFORMATION_READ_DEALER = 'Dealer_Connect::dealer_orders_misc';

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $isDealer = $this->customerSession->getCustomer()->isDealer();
        $dealerHelper = $this->dealerHelper;
        $canShowCusPrice = $dealerHelper->checkCustomerCusPriceAllowed();
        $canShowMargin = $dealerHelper->checkCustomerMarginAllowed();
        $columns = $observer->getEvent()->getColumns();
        $lineValue = $columns->getLineValue();
        $dealerLineValue = $columns->getDealerLineValue();

        $type =$observer->getEvent()->getType();
        /* @var $columns Varien_Object */

        //can show Misc Charges
        $code = $dealerHelper->isDealerPortal() ? static::FRONTEND_RESOURCE_INFORMATION_READ_DEALER : static::FRONTEND_RESOURCE_INFORMATION_READ;
        $code = $type === 'invoice' ? static::FRONTEND_RESOURCE_INFORMATION_READ_INVOICE : $code;
        $isMiscAllowed = $this->_accessauthorization->isAllowed($code);
        $miscGlobal = $isMiscAllowed && $this->scopeConfig->getValue('customerconnect_enabled_messages/crq_options/allow_misc_charges', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($miscGlobal){
            $lineValue['index'] = 'misc_line_total';
            $dealerLineValue['index'] = 'misc_line_total_d';
        }

        if(!$isMiscAllowed){
            $columns->unsetData('miscellaneous_charges_total');
        }

        if ($isDealer && $dealerHelper->isDealerPortal()) {
            $currentMode = $this->customerSession->getDealerCurrentMode();
            $price = $columns->getPrice();
            $dealerPrice = $columns->getDealerPrice();

            $dealerPrice['header'] = $currentMode === "dealer" ? 'Customer Price<br>Discount' : 'Price';
            $price['header'] = ($currentMode === "dealer" && $canShowMargin !== "disable") ? 'Price<br>Margin' : 'Price';
            $dealerPrice['align'] = 'left';
            $price['align'] = 'left';
            $dealerLineValue['align'] = 'left';
            $lineValue['align'] = 'left';
            $dealerPrice['column_css_class'] = ($currentMode === "dealer" && $canShowCusPrice === "disable") ? "no-display" : "";
            $dealerPrice['header_css_class'] = ($currentMode === "dealer" && $canShowCusPrice === "disable") ? "no-display" : "";
            $dealerLineValue['column_css_class'] = $currentMode === "dealer" ? "no-display" : "";
            $dealerLineValue['header_css_class'] = $currentMode === "dealer" ? "no-display" : "";
            $price['column_css_class'] = $currentMode === "shopper" ? "no-display" : "";
            $price['header_css_class'] = $currentMode === "shopper" ? "no-display" : "";
            $lineValue['column_css_class'] = $currentMode === "shopper" ? "no-display" : "";
            $lineValue['header_css_class'] = $currentMode === "shopper" ? "no-display" : "";
            $dealerPrice['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Orders\Details\Lines\Renderer\Currency';
            $price['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Orders\Details\Lines\Renderer\Currency';

            $columns->setPrice($price);
            $columns->setDealerPrice($dealerPrice);
        }

        $columns->setLineValue($lineValue);
        if($type !== 'invoice'){
            $columns->setDealerLineValue($dealerLineValue);
        }
    }

}
