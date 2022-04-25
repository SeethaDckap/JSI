<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class FillNewCategoryProductColumns extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Triggered when the reorder button is clicked 
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

        $collection = $observer->getEvent()->getCollection();
        if ($this->request->getRequestedControllerName() == 'catalog_category') {
            $store = $this->_getStore();
            $storeId = $store->getId() ? $store->getId() : null;

            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $storeId);
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $storeId);
        }
        return $collection;
    }

}