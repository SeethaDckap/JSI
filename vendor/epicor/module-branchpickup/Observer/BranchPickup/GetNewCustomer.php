<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

class GetNewCustomer extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Clear Branch Pickup, If the user ends Masquerade 
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isBranchSelected = $this->_helper->getSelectedBranch();
        if (($observer->getQuote()->getData('checkout_method') == \Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER) && ($isBranchSelected)) {
            $order = $observer->getEvent()->getOrder();
            $customer_id = $order->getCustomerId();
            $customerData = $this->customerCustomerFactory->create()->load($customer_id);
            $customerData->setEccIsBranchPickupAllowed(2);
            $customerData->save();
        }
    }

}