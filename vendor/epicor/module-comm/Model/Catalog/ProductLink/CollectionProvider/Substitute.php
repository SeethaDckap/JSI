<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Catalog\ProductLink\CollectionProvider;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\CollectionProviderInterface;

/**
 * Class Substitute
 * @package Epicor\Comm\Model\Catalog\ProductLink\CollectionProvider
 */
class Substitute implements CollectionProviderInterface
{
    /**
     * @var \Epicor\Comm\Model\Substitute
     */
    protected $substituteModel;

    /**
     * Substitute constructor.
     * @param \Epicor\Comm\Model\Substitute $substituteModel
     */
    public function __construct(
        \Epicor\Comm\Model\Substitute $substituteModel
    ) {
        $this->substituteModel = $substituteModel;
    }

    /**
     * @param Product $product
     * @return array|Product[]
     */
    public function getLinkedProducts(Product $product)
    {
        return (array)$this->substituteModel->getSubstituteProducts($product);
    }
}
