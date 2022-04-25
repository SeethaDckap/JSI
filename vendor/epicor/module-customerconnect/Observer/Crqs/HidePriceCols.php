<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Observer\Crqs;

class HidePriceCols extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $isHidePrice = $this->commHelper->getEccHidePrice();

        if ($isHidePrice && $isHidePrice != 2) {
            $columns = $observer->getEvent()->getColumns();
            /* @var $columns Varien_Object */
            $originalValue = $columns->getOriginalValue();
            $originalValue['column_css_class'] = "no-display";
            $originalValue['header_css_class'] = "no-display";
            $columns->setOriginalValue($originalValue);
        }

    }
}
