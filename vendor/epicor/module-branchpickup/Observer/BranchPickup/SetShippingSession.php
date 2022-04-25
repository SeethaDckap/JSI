<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class SetShippingSession extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Clear Branch Pickup, If the user ends Masquerade 
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $quote = $event->getQuote();
        $request = $event->getRequest();
        $getShippingMethod = $request->getPost('shipping_method', false);
        if ($getShippingMethod == \Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup::ECC_BRANCHPICKUP_COMBINE) {
            $getLocationCode = $request->getPost('branch_pickup', false);
            $helper = $this->_helper;
            /* @var $helper Epicor_BranchPickup_Helper_Data */
            $helper->selectBranchPickup($getLocationCode);
        }
    }

}