<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class EndMasqueradeBranchPickup extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Clear Branch Pickup, If the user ends Masquerade 
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper \Epicor\SalesRep\Helper\Data */
        if (!$salesRepHelper->isEnabled()) {
            return;
        }
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */
        if ($customer->isSalesRep()) {
            $helper = $this->_helper;
            /* @var $helper \Epicor\BranchPickup\Helper\Data */
            $helper->emptyBranchPickup();
        }
    }

}