<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Catalog\Product\View\Type\Bundle;

class HideOptionPrice
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Epicor\Comm\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * Adds Decimal Validation
     *
     * @return array
     */
    public function afterGetSelectionTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $html
    ) {
        $hidePrices = $this->helper->getEccHidePrice();
        $priceDisplayDisabled = $this->helper->isPriceDisplayDisabled();
        if (($hidePrices && $hidePrices != 2) || $priceDisplayDisabled) {
            return '';
        }
        return $html;
    }
}
