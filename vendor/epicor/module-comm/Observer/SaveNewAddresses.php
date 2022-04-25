<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SaveNewAddresses extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Triggered when the base url changes, sends a SYN message with the provided new url
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // used in checkout_onepage_controller_success_action
        $customerSession = $this->customerSessionFactory->create();
        /* @var $customerSession \Magento\Customer\Model\Session */
        $helper = $this->commHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Data */
        $erpAccount = $helper->getErpAccountInfo();
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
        $erpCode = $erpAccount->getCompany() . "_" . $erpAccount->getShortCode();
        $saveNewAddress = $this->scopeConfig->getValue("Epicor_Comm/save_new_addresses/{$erpCode}", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);    // retrieve erp level check first, but if it doesn't exist get global 
        if (!isset($saveNewAddress)) {
            $saveNewAddress = $this->scopeConfig->getValue('Epicor_Comm/save_new_addresses/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        if ($saveNewAddress) {                                // continue only if config states address is to be saved
            $newBillingAddress = $customerSession->getSaveBillingAddressToErp();
            $newShippingAddress = $customerSession->getSaveShippingAddressToErp();
            $orderId = $observer->getEvent()->getOrderIds();
            $order = $this->salesOrderFactory->create()->load($orderId[0]);
            $isInvoice = true;
            $isDelivery = true;
            switch (true) {
                case ($newBillingAddress && $newShippingAddress):
                    $isDelivery = false;
                    $this->commHelper->addNewErpAddress($saveNewAddress, $isInvoice, $isDelivery);
                    $isInvoice = false;
                    $isDelivery = true;
                    $this->commHelper->addNewErpAddress($saveNewAddress, $isInvoice, $isDelivery);
                    break;
                case ($newBillingAddress):
                    $isDelivery = false;
                    $this->commHelper->addNewErpAddress($saveNewAddress, $isInvoice, $isDelivery);
                    break;
                case ($newShippingAddress):
                    $isInvoice = false;
                    $this->commHelper->addNewErpAddress($saveNewAddress, $isInvoice, $isDelivery);
                    break;
            }
        }
    }

}