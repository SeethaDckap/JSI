<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Locations;

class FilterLocations extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getLocations();
        /* @var $collection Epicor_Comm_Model_Resource_Locations */

        $store = $this->storeManager->getStore();
        /* @var $store Epicor_Comm_Model_Store */

        if (!$collection->getFlag('ignore_stores')) {
            $collection->addFieldToFilter('code', array('in' => $store->getAllowedLocationCodes()));
        }
        return $this;
    }

}