<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class CatalogCategoryCollectionLoadAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove hidden caegories from the collection
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCategoryCollection();
        $collection->loadProductCount($collection->getItems(), true, true);
        foreach ($collection as $key => $item) {
            if ($item->getEntityTypeId() == 3) {
                $category = $this->catalogCategoryFactory->create()->load($item->getId());
                $products = $this->catalogResourceModelProductCollectionFactory->create()
                    ->addCategoryFilter($category);
                $prodcount = $products->count();
                if ($prodcount < 1) {
                    $collection->removeItemByKey($key);
                }
            }
        }
    }

}