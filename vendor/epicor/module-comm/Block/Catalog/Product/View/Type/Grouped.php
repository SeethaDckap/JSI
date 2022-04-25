<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
/**
 * Catalog grouped product info block
 */
namespace Epicor\Comm\Block\Catalog\Product\View\Type;

class Grouped extends \Magento\Catalog\Block\Product\View\AbstractView{
    public function getAssociatedProducts()
    {
        $_associatedProducts =  $this->getProduct()->getTypeInstance()->getAssociatedProducts($this->getProduct());
        $_associatedProducts = array_filter($_associatedProducts, function ($arrayValue) {
            return $arrayValue->isSaleable();
        });
        return $_associatedProducts;
    }
}