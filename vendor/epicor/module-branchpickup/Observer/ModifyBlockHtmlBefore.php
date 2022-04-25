<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer;

class ModifyBlockHtmlBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove the Branch Pickup Selection link in Account Dropdown
     * 
     * 
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof \Magento\Framework\View\Element\Html\Links) {
            $branchpickupEnabled = $this->_helper->isBranchPickupAvailable();
            //$isLoggedIn          = Mage::helper('customer')->isLoggedIn();
            /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
            if (!$branchpickupEnabled) {
                $url = $block->getUrl('branchpickup/pickup/select', array(
                    '_secure' => true
                ));
               // $block->removeLinkByUrl($url); // not supported in the Magento2
            }
        }
    }

}