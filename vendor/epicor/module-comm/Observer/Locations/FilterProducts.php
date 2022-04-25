<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Locations;

class FilterProducts extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->commLocationsHelper;
        /* @var $helper Epicor_Comm_Helper_Locations */
        if ($helper->isLocationsEnabled()) {

            $collection = $observer->getEvent()->getCollection();
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

            if (
                $collection->getFlag('no_product_filtering') ||
                $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection || $collection->getFlag('no_location_filtering')
            ) {
                return $this;
            }

            $locationTable = $collection->getTable('ecc_location_product');
            $locationString = $helper->getEscapedCustomerDisplayLocationCodes();
            $collection->getSelect()->where(
                '(SELECT COUNT(*) FROM ' . $locationTable . ' AS `locations` WHERE locations.product_id = e.entity_id AND locations.location_code IN (' . $locationString . ')) > 0'
            );
            $this->registry->unregister('location_sql_applied');
            $this->registry->register('location_sql_applied', true);
        }

        return $this;
    }

}