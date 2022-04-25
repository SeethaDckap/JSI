<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Observer;

class SupplierErpAccountEntityRegisterRemove extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $supplier = $observer->getEvent()->getErpaccount();

        if ($supplier->isTypeSupplier()) {
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            $this->removeEntityRegistration($supplier, 'SupplierErpAccount');
        }
    }

}