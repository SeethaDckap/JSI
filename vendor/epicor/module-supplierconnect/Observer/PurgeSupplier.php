<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Observer;

class PurgeSupplier extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = $observer->getEvent()->getItems();

        foreach ($items as $item) {
            $this->purgeItem($item, 'customer/customer', 'supplier');
        }
    }

}