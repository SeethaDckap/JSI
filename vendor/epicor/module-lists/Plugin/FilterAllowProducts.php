<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Plugin;

use Epicor\Lists\Helper\Frontend\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;

class FilterAllowProducts
{
    private $productHelper;
    private $allowedProducts = [];
    private $allowedListProductIds = [];
    private $scopeConfig;

    public function __construct(
        ProductHelper $productHelper,
        ScopeConfigInterface $scopeConfig
    ){
        $this->productHelper = $productHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterGetAllowProducts($subject, $result)
    {
        $this->allowedProducts = [];
        if (!$this->isListsEnabled()) {
            return $result;
        }
        $this->allowedListProductIds = $this->productHelper->getActiveListsProductIds(true);
        if ($this->isValidListProductIds()) {
            $this->setAllowedListProducts($result);
        } else {
            $this->allowedProducts = $result;
        }

        return $this->allowedProducts;
    }

    public function afterGetCacheKeyInfo($subject, $result){

        if (!$this->isListsEnabled()) {
            return $result;
        }
        $productsAllowed = $subject->getAllowProducts();
        $result[] = $this->getAllowedProductIdsString($productsAllowed);

        return $result;
    }

    private function getAllowedProductIdsString($productsAllowed): string
    {
        $allowedIdString = '';
        if(is_array($productsAllowed)){
            $allowedIdString = $this->getProductIdString($productsAllowed);
        }

        return $allowedIdString;
    }

    private function getProductIdString($productsAllowed): string
    {
        $productIdString = '';
        foreach($productsAllowed as $product){
            if($product instanceof Product){
                $productIdString .= (string) $product->getId();
            }
        }

        return $productIdString;
    }

    private function isListsEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('epicor_lists/global/enabled');
    }

    private function isValidListProductIds()
    {
        return !empty($this->allowedListProductIds) && !$this->isSingleZeroArray($this->allowedListProductIds);
    }

    private function isSingleZeroArray($array)
    {
        return is_array($array) && isset($array[0]) && $array[0] === '0' && count($array) === 1;
    }

    private function setAllowedListProducts(array $allowedProducts)
    {
        foreach ($allowedProducts as $product) {
            $this->addAllowedListProduct($product);
        }
    }

    private function addAllowedListProduct(Product $product)
    {
        if (in_array($product->getId(), $this->allowedListProductIds)) {
            $this->allowedProducts[] = $product;
        }
    }
}
