<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Catalog\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\CopyConstructorInterface;

/**
 * Class substitute
 * @package Epicor\Comm\Model\Catalog\Product\CopyConstructor
 */
class substitute implements CopyConstructorInterface
{
    /**
     * Build product links
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    public function build(Product $product, Product $duplicate)
    {
        $data = [];
        $attributes = [];

        $link = $product->getLinkInstance();
        /** @var $link Link */
        $link->useSubstituteLinks();
        foreach ($link->getAttributes() as $attribute) {
            if (isset($attribute['code'])) {
                $attributes[] = $attribute['code'];
            }
        }

        foreach ($product->getSubstituteLinkCollection() as $link) {
            $data[$link->getLinkedProductId()] = $link->toArray($attributes);
        }

        $duplicate->setSubstituteLinkData($data);
    }
}
