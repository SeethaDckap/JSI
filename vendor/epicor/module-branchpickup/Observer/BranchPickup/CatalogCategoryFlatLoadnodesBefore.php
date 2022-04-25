<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class CatalogCategoryFlatLoadnodesBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove hidden caegories from the collection
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $select = $observer->getEvent()->getSelect();
        $select->columns("display_mode");
    }

}