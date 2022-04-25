<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Locations;

class SetCartItemLocation extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer_Locations
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $request = $this->request;
        $locationCode = $request->getParam('location_code');
        if (!empty($locationCode)) {
            $item->setEccLocationCode($locationCode);
        }
        return $this;
    }

}