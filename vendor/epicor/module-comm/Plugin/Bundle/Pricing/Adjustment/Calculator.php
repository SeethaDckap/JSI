<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Bundle\Pricing\Adjustment;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Catalog\Model\Product;

class Calculator {

    public function beforeGetAmount(\Magento\Bundle\Pricing\Adjustment\Calculator $subject, $amount, SaleableInterface $saleableItem, $exclude = null, $context = []) {
        $amount = $saleableItem->getFinalPrice();
        return array($amount, $saleableItem, $exclude, $context);
    }

    public function beforeGetMaxAmount(\Magento\Bundle\Pricing\Adjustment\Calculator $subject, $amount, Product $saleableItem, $exclude = null) {
        $amount = $saleableItem->getFinalPrice();
        return array($amount, $saleableItem, $exclude);
    }

    public function beforeGetAmountWithoutOption(\Magento\Bundle\Pricing\Adjustment\Calculator $subject, $amount, Product $saleableItem) {
        $amount = $saleableItem->getFinalPrice();
        return array($amount, $saleableItem);
    }

}
