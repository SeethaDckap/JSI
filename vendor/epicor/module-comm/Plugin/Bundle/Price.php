<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Bundle;

class Price
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    )
    {
        $this->registry = $registry;
    }

    /**
     * Written not to consider the special price set from MSQ
     * @param \Magento\Bundle\Model\Product\Price $subject
     * @param \Closure $proceed
     * @param $finalPrice
     * @param $specialPrice
     * @param $specialPriceFrom
     * @param $specialPriceTo
     * @param null $store
     * @return mixed
     */
    public function aroundCalculateSpecialPrice(
        \Magento\Bundle\Model\Product\Price $subject,
        \Closure $proceed,
        $finalPrice,
        $specialPrice,
        $specialPriceFrom,
        $specialPriceTo,
        $store = null
    )
    {
        if ($specialPrice !== null && $specialPrice != false && $this->registry->registry('special_price_from_msq')) {
            $finalPrice = min($finalPrice, $specialPrice);
            return $finalPrice;
        }
        $finalPrice = $proceed($finalPrice, $specialPrice, $specialPriceFrom, $specialPriceTo, $store);
        return $finalPrice;
    }
}


