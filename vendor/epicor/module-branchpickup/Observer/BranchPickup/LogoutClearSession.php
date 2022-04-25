<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class LogoutClearSession extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Any actions required after logout
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $sessionHelper = $this->branchPickupSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->clear();
    }

}