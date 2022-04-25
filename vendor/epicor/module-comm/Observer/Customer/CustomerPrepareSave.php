<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Customer;

class CustomerPrepareSave extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        $request = $observer->getEvent()->getRequest();
        $address = $request->getParam('address');

        if (empty($address) || !isset($address['_template_']) && $customer->getAddresses()) {
            if($customer->getAddresses()) {
                foreach ($customer->getAddresses() as $customerAddress) {
                    if ($customerAddress->getId()) {
                        $customerAddress->setData('_deleted', false);
                    }
                }
            }
        }
        $this->processLists($observer);
        $this->processLocations($observer);

        return $this;
    }

}