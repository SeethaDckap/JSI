<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Customer;

class LogoutActions extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerSession = $this->customerSession;
        $customerSession->setManageSalesRepAccountId(false);
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        if ($customer->isSalesRep()) { 
            $helper = $this->salesRepHelper;
            /* @var $helper Epicor_SalesRep_Helper_Data */
            $helper->wipeCart();
        }
        
       
    }

}