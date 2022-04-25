<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class CheckBranchSelected extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Clear Branch Pickup, If the user ends Masquerade 
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $selectedBranch = $this->_helper->getSelectedBranch();
        if ($selectedBranch) {
            $this->_helper->selectBranchPickup(null);
            $this->_helper->resetBranchLocationFilter();
            $error = 'Branch Pickup was not supported';
            $helper = $this->commHelper;
            /* @var $helper Epicor_Comm_Helper_Data */
            if ($helper->errorExists($error) == false) {
                $this->messageManager->addErrorMessage($error);
            }
        }
    }

}