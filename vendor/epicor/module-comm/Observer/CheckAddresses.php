<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class CheckAddresses extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();
        $optionalFields = array('mobile_phone' => 'mobile_number', 'email' => 'email');  // add new fields here format: addressfield=>config definition
        foreach ($optionalFields as $key => $value) {
            if (!$this->scopeConfig->isSetFlag("customer/address/display_{$key}", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                if ($address->getData($value)) {
                    if(!$this->registry->registry("sou_shipment_email")){
                        unset($address[$value]);
                    }
                }
            }
        }
        return;
    }

}