<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Supplierconnect\Observer;

class SetupSupplierLayout extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $customerSession;


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $this->customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */
        if ($customer->isSupplier()) {
            $supplierHandle = 'supplierconnect_layout_enabled';
            //$handle = sprintf('salesrep_layout_enabled', $page);
            $update = $observer->getEvent()->getLayout()->getUpdate();
            //$update->addHandle($handle);
            $update->addHandle($supplierHandle);
        }
    }

}