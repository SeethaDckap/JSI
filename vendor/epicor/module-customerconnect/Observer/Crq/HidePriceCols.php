<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Observer\Crq;

class HidePriceCols extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $isHidePrice = $this->commHelper->getEccHidePrice();

        if ($isHidePrice && in_array($isHidePrice, [1,2,3])) {
            $columns = $observer->getEvent()->getColumns();
            /* @var $columns Varien_Object */
            $lineValue = $columns->getLineValue();
            $price = $columns->getPrice();
            $price['column_css_class'] = "no-display";
            $price['header_css_class'] = "no-display";
            $lineValue['column_css_class'] = "no-display";
            $lineValue['header_css_class'] = "no-display";
            $columns->setLineValue($lineValue);
            $columns->setPrice($price);
        }

    }
}
