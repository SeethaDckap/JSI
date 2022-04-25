<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class CatalogCategoryCollectionLoadBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove hidden caegories from the collection
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCategoryCollection();
        $collection->addAttributeToSelect("display_mode");
        $collection->addAttributeToSelect("is_anchor");
    }

}