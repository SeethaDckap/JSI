<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Access;

class GetSalesRepAccessGroups extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        if ($customer->isSalesRep()) {
            $transport = $observer->getEvent()->getTransport();
            /* @var $transport Varien_Object */
            $groups = $this->scopeConfig->getValue('epicor_common/accessrights/salesrep_default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $groups = !empty($groups) ? explode(',', $groups) : array();
            $transport->setGroups($groups);
        }
    }

}