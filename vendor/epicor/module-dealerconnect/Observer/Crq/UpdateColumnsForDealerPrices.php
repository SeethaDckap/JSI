<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Observer\Crq;

class UpdateColumnsForDealerPrices extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $isDealer = $this->customerSession->getCustomer()->isDealer();
        $currentMode = $this->customerSession->getDealerCurrentMode();
        $dealerHelper = $this->dealerHelper;
        $canShowCusPrice = $this->dealerHelper->checkCustomerCusPriceAllowed();
        $canShowMargin = $dealerHelper->checkCustomerMarginAllowed();

        if ($isDealer && $dealerHelper->isDealerQuotePortal()) {
            $columns = $observer->getEvent()->getColumns();
            /* @var $columns Varien_Object */
            $lineValue = $columns->getLineValue();
            $price = $columns->getPrice();
            $dealerPrice = $columns->getDealerPrice();
            if ($this->registry->registry('rfqs_editable')) {
                $lineValue['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines\Renderer\Currency';
                $dealerPrice['column_css_class'] = "";
                $dealerPrice['header_css_class'] = "";
                if ($currentMode === "shopper") {
                    $price['column_css_class'] = "no-display";
                    $price['header_css_class'] = "no-display";
                    $dealerPrice['header'] = "Price";
                } else {
                    if ($canShowCusPrice === "disable") {
                        $dealerPrice['column_css_class'] = "no-display";
                        $dealerPrice['header_css_class'] = "no-display";
                    }
                }
                $dealerPrice['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines\Renderer\Currency';
                $price['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines\Renderer\Currency';
            } else {
                $dealerPrice['header'] = $currentMode === "dealer" ? 'Customer Price<br>Discount' : 'Price';
                $price['header'] = ($currentMode === "dealer" && $canShowMargin !== "disable") ? 'Price<br>Margin' : 'Price';

                $dealerPrice['align'] = 'left';
                $price['align'] = 'left';
                $lineValue['align'] = 'left';

                $dealerPrice['column_css_class'] = ($currentMode === "dealer" && $canShowCusPrice === "disable") ? "no-display" : "";
                $dealerPrice['header_css_class'] = ($currentMode === "dealer" && $canShowCusPrice === "disable") ? "no-display" : "";
                $dealerLineValue['column_css_class'] = $currentMode === "dealer" ? "no-display" : "";
                $dealerLineValue['header_css_class'] = $currentMode === "dealer" ? "no-display" : "";
                $price['column_css_class'] = $currentMode === "shopper" ? "no-display" : "";
                $price['header_css_class'] = $currentMode === "shopper" ? "no-display" : "";

                $dealerPrice['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Orders\Details\Lines\Renderer\Currency';
                $price['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Orders\Details\Lines\Renderer\Currency';
                $dealerPrice['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Quotes\Details\Confirmed\Lines\Renderer\Currency';
                $price['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Quotes\Details\Confirmed\Lines\Renderer\Currency';
                $lineValue['renderer'] = 'Epicor\Dealerconnect\Block\Customer\Quotes\Details\Confirmed\Lines\Renderer\Currency';
            }
            $columns->setLineValue($lineValue);
            $columns->setPrice($price);
            $columns->setdealerPrice($dealerPrice);
        }
    }

}
