<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ChangeCustomerTaxButton extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Triggered when the reorder button is clicked 
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block->getType() == 'adminhtml/tax_class') {
            $block->updateButton('add', 'label', __('Add New Class'));
        }
    }

}