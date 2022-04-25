<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class AddMobileToAddress extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Handler for controller_action_predispatch event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();

        // if no email on address, check erpAddress table 
        if (!$address->getEccMobileNumber()) {
            $erpAddressCode = $address->getEccErpAddressCode();
            $erpAddressInfo = $this->commCustomerErpaccountAddressFactory->create()->load($erpAddressCode, 'erp_code');
            if ($erpAddressInfo->getMobileNumber()) {
                $address->getEccMobileNumber($erpAddressInfo->getMobileNumber());
            }
        }
    }

}