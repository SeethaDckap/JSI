<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Customer;

class CanMasqueradeAs extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper \Epicor\SalesRep\Helper\Data */

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customer->isSalesRep()) {

            $salesRepAccount = $this->salesRepAccountFactory->create()->load($customer->getEccSalesRepAccountId());
            /* @var $salesRepAccount \Epicor\SalesRep\Model\Account */

            $erpAccountId = $observer->getEvent()->getErpAccountId();
            $transport = $observer->getEvent()->getTransport();
            $masqueradeAs = $salesRepAccount->canMasqueradeAs($erpAccountId);
            $transport->setMasqueradeAs($masqueradeAs);
        }
    }

}