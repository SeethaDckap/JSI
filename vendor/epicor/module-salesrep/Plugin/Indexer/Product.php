<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\SalesRep\Plugin\Indexer;

class Product extends AbstractPlugin
{

    /**
     * Reindex on product save
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     */
    public function aroundSave(
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $product
    )
    {
        return $this->addCommitCallback(
            $productResource,
            $proceed,
            $product
        );
    }

    /**
     * Reindex on product delete
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     */
    public function aroundDelete(
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $product
    )
    {
        return $this->addCommitCallback(
            $productResource,
            $proceed,
            $product
        );
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @throws \Exception
     */
    private function addCommitCallback(
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $product
    )
    {
        try {
            $productResource->beginTransaction();
            $result = $proceed($product);
            $productResource->addCommitCallback(function () use ($product) {
                $this->reindex();
            });
            $productResource->commit();
        } catch (\Exception $e) {
            $productResource->rollBack();
            throw $e;
        }

        return $result;
    }

}
