<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Observer;

class SupplierEntityRegisterRemove extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->removeEntityRegistration($observer->getEvent()->getCustomer(), 'Supplier');
    }

}