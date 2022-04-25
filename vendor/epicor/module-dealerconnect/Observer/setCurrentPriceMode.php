<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Observer;

class setCurrentPriceMode extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    protected $dealerHelper;
    protected $customerSession;

    public function __construct(
    \Magento\Framework\Registry $registry, \Epicor\Dealerconnect\Helper\Data $dealerHelper, \Magento\Customer\Model\Session $customerSession
    ) {
        $this->registry = $registry;
        $this->dealerHelper = $dealerHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * sets current price mode for customer type dealer
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $customer = $observer->getEvent()->getCustomer();
        $dealerHelper = $this->dealerHelper;
        if ($customer->isDealer()) {
            $currentMode = $dealerHelper->checkCustomerLoginModeType();
            $this->customerSession->setDealerCurrentMode($currentMode);
        }
        return $this;
    }

}
