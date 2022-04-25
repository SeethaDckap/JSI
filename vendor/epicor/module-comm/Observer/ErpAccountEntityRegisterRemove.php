<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ErpAccountEntityRegisterRemove extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $erpAccount = $observer->getEvent()->getErpaccount();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        if ($erpAccount->isTypeCustomer()) {
            $this->removeEntityRegistration($erpAccount, 'ErpAccount');

            if ($this->registry->registry('erpaccount_' . $erpAccount->getId() . 'addresses_to_be_deleted')) {
                $addresses = $this->registry->registry('erpaccount_' . $erpAccount->getId() . 'addresses_to_be_deleted');
                foreach ($addresses as $address) {
                    $this->removeEntityRegistration($address, 'ErpAddress');
                }
            }
        }
    }

}