<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\SalesRep\Plugin\Indexer;

class Category extends AbstractPlugin
{
    
    /**
     * @param \Magento\Catalog\Model\Category $subject
     * @param \Magento\Catalog\Model\Category $result
     * @return \Magento\Catalog\Model\Category
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Model\Category $subject,
        \Magento\Catalog\Model\Category $result
    ) {
        /** @var \Magento\Catalog\Model\Category $result */
        $productIds = $result->getAffectedProductIds();
        if ($productIds) {
            $this->reindex();
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Category $subject
     * @param \Magento\Catalog\Model\Category $result
     * @return \Magento\Catalog\Model\Category
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Catalog\Model\Category $subject,
        \Magento\Catalog\Model\Category $result
    ) {
        $this->reindex();
        return $result;
    }

}
