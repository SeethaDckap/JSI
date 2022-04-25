<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Access;

class SetupSalesRepLayout extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $page = (int) $this->request->getParam('id');

        $customer = $this->customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        $helper = $this->commHelper;

        if ($customer->isSalesRep()) {
            if ($helper->isMasquerading()) {
                $masqueradeHandle = sprintf('salesrep_layout_masquerading', $page);
            } else {
                $masqueradeHandle = sprintf('salesrep_layout_not_masquerading', $page);
            }

            $handle = sprintf('salesrep_layout_enabled', $page);
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $update->addHandle($handle);
            $update->addHandle($masqueradeHandle);
        } else {
            $handle = sprintf('salesrep_layout_disabled', $page);
            $update = $observer->getEvent()->getLayout()->getUpdate();
            $update->addHandle($handle);
        }
    }

}