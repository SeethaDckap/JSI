<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class RemoveLocationPicker extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Clear Branch Pickup, If the user ends Masquerade 
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getLayout();
        $branchpickupEnabled = $this->_helper->isBranchPickupAvailable();
        $isBranchSelected = $this->_helper->getSelectedBranch();
        $block = $layout->getBlock('epicor_comm.locationpicker');
        if (($branchpickupEnabled) && !empty($isBranchSelected)) {
            $layout->unsetElement('epicor_comm.locationpicker');
        }
    }

}