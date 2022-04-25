<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Locations;

class AddLocationVariables extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer_Locations
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        /* @var $block Mage_Core_Block_Abstract */

        $locHelper = $this->commLocationsHelper;
        /* @var $locHelper Epicor_Comm_Helper_Locations */

        $showLoc = $locHelper->isLocationsEnabled();
        $showLocInColumn = false;
        
        $module = $this->request->getModuleName();
        
        //If the module is multishipping there is no need for us to look below conditions
        if($module =="multishipping") {
            return $this;
        }     
        
        //If the Location was disabled then the colspan should be 5
        if(!$showLoc) {
            $block->setLabelProperties('colspan="4" class="a-right mark"');
            $block->setValueProperties('colspan="4" class="a-right mark"');
            return $this;
        }           

        $controller = $this->request->getControllerName();
        //$action = Mage::app()->getRequest()->getActionName();

        if ($block instanceof \Magento\Checkout\Block\Cart || ($block instanceof \Magento\Checkout\Block\Cart\Item\Renderer && $controller != 'onepage')) {
            $showLoc = ($showLoc) ? $locHelper->showIn('cart') : false;
            $showLocInColumn = ($showLoc) ? $locHelper->showColumnIn('cart') : false;
        } else if ($block instanceof Mage_Checkout_Block_Onepage_Review_Info || ($block instanceof \Magento\Checkout\Block\Cart\Item\Renderer && $controller == 'onepage') || $block instanceof \Magento\Checkout\Block\Cart\Totals) {
            $showLoc = ($showLoc) ? $locHelper->showIn('checkout') : false;
            $showLocInColumn = ($showLoc) ? $locHelper->showColumnIn('checkout') : false;
        } else if ($block instanceof \Magento\Sales\Block\Order\Items || $block instanceof \Magento\Sales\Block\Order\PrintShipment || $block instanceof \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer || $block instanceof \Magento\Sales\Block\Order\Totals || $block instanceof \Magento\Sales\Block\Order\Invoice\Items || $block instanceof \Magento\Sales\Block\Order\Invoice || $block instanceof \Magento\Sales\Block\Order\PrintOrder\Shipment || $block instanceof \Magento\Sales\Block\Order\PrintOrder\Invoice || $block instanceof \Magento\Shipping\Block\Order\Shipment || $block instanceof \Magento\Shipping\Block\Items) {
            $showLoc = ($showLoc) ? $locHelper->showIn('order_details') : false;
            $showLocInColumn = ($showLoc) ? $locHelper->showColumnIn('order_details') : false;
            if ($block instanceof \Magento\Sales\Block\Order\Totals) {
                if ($showLocInColumn) {
                   $block->setLabelProperties('colspan="4" class="a-right mark"');
                   $block->setValueProperties('colspan="4" class="a-right mark"');
                }
            }
        }

        $block->setShowLocations($showLoc);
        $block->setShowLocationsColumn($showLocInColumn);

        if ($showLoc && $block instanceof \Magento\Checkout\Block\Cart\Item\Renderer || $block instanceof \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer) {
            /* @var $block Mage_Checkout_Block_Cart_Item_Renderer */
            $item = $block->getItem();
            $block->setItemLocationName($locHelper->getLocationName($item->getEccLocationCode()));
        }

        return $this;
    }

}