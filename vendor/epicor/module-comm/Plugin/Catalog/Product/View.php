<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Catalog\Product;

class View 
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
    public function afterGetQuantityValidators(
        \Magento\Catalog\Block\Product\View $subject,
        array  $output
    )
    {
        $_product = $subject->getProduct();
        $decimalPlaces = $this->helper->getDecimalPlaces($_product);
        if ($decimalPlaces !== '') {
            $output['validatedecimalplace'] = $decimalPlaces;
        }
        return $output;
    }
}
