<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;

use Magento\Framework\DataObject;
use Magento\Catalog\Model\Product;
use Epicor\Comm\Model\Catalog\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection;

/**
 * Class Substitute
 * @package Epicor\Comm\Model
 */
class Substitute extends DataObject
{
    /**
     * Product link instance
     *
     * @var Product\Link
     */
    protected $linkInstance;

    /**
     * Substitute constructor.
     * @param Link $productLink
     */
    public function __construct(
        Link $productLink
    ) {
        $this->linkInstance = $productLink;
    }

    /**
     * Retrieve link instance
     *
     * @return  Product\Link
     */
    public function getLinkInstance()
    {
        return $this->linkInstance;
    }

    /**
     * Retrieve array of Substitute products
     *
     * @param Product $currentProduct
     * @return array
     */
    public function getSubstituteProducts(Product $currentProduct)
    {
        if ( ! $this->hasSubstituteProducts()) {
            $products = [];
            $collection = $this->getSubstituteProductCollection($currentProduct);
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setSubstituteProducts($products);
        }
        return $this->getData('substitute_products');
    }

    /**
     * Retrieve substitute products identifiers
     *
     * @param Product $currentProduct
     * @return array
     */
    public function getSubstituteProductIds(Product $currentProduct)
    {
        if ( ! $this->hasSubstituteProductIds()) {
            $ids = [];
            foreach ($this->getSubstituteProducts($currentProduct) as $product) {
                $ids[] = $product->getId();
            }
            $this->setSubstituteProductIds($ids);
        }
        return $this->getData('substitute_product_ids');
    }

    /**
     * Retrieve collection substitute product
     *
     * @param Product|null $currentProduct
     * @return mixed
     */
    public function getSubstituteProductCollection(Product $currentProduct = null)
    {
        $collection = $this->getLinkInstance()->useSubstituteLinks()->getProductCollection()->setIsStrongMode();
        if($currentProduct) {
            $collection->setProduct($currentProduct);
        }
        return $collection;
    }

    /**
     * Retrieve collection substitute link
     *
     * @param Product $currentProduct
     * @return Collection
     */
    public function getSubstituteLinkCollection(Product $currentProduct)
    {
        $collection = $this->getLinkInstance()->useSubstituteLinks()->getLinkCollection();
        $collection->setProduct($currentProduct);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }
}
