<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Observer;

class SupplierErpAccountEntityRegisterUpdate extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->registry->registry('entity_register_update_suppliererpaccount')) {

            $supplier = $observer->getEvent()->getErpaccount();

            if ($supplier->isTypeSupplier()) {
                $this->updateEntityRegistration($supplier, 'SupplierErpAccount');
            }
        }
    }

}