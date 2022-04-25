<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class PaymentMethodIsActive extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $method = $event->getMethodInstance();
        $result = $event->getResult();
        $validPaymentMethods = array();
        $invalidPaymentMethods = array();
        $allowedTypes = array();
        $erpAccountId = $this->commHelper->create()->getErpAccountId();

        if ($erpAccountId) {
            $erpGroup = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
            $getAccountType = $erpGroup->getAccountType();
            $allowedTypes = array("B2B", "B2C", "Dealer");
            if (in_array($getAccountType, $allowedTypes)) {
                if (!(is_null($erpGroup->getAllowedPaymentMethods()) &&
                    is_null($erpGroup->getAllowedPaymentMethodsExclude()))) {

                    $exclude = !is_null($erpGroup->getAllowedPaymentMethods()) ? 'N' : 'Y';
                    $validPaymentMethods = unserialize($erpGroup->getAllowedPaymentMethods());
                    $invalidPaymentMethods = unserialize($erpGroup->getAllowedPaymentMethodsExclude());

                    if ($exclude == 'N') {
                        if (!in_array($method->getCode(), $validPaymentMethods)) {
                            $result->setData('is_available', true);
                        }
                    } else {
                        if (in_array($method->getCode(), $invalidPaymentMethods)) {
                            $result->setData('is_available', true);
                        }
                    }
                }
            }
        }
    }

}