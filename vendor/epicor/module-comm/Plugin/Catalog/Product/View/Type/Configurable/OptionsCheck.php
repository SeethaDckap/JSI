<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Catalog\Product\View\Type\Configurable;

class OptionsCheck
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $helper;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct = null;

    public function __construct(
        \Epicor\Comm\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProduct
    )
    {
        $this->helper = $helper;
        $this->catalogProduct = $catalogProduct;
    }

    /**
     * Adds Decimal Validation
     *
     * @return array
     */
    public function afterGetAllowProducts(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        array $output
    )
    {
        $products = [];
        $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();
        $allProducts = $subject->getProduct()->getTypeInstance()->getUsedProducts($subject->getProduct(), null);
        foreach ($allProducts as $product) {
            if ($product->isSalable() || $skipSaleableCheck) {
                $products[] = $product;
            }
        }
        $subject->setAllowProducts($products);

        return $subject->getData('allow_products');
    }
}
