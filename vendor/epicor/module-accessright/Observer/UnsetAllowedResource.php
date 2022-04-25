<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Observer;

class UnsetAllowedResource implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
    \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * Clears the price mode value for customer type dealer
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->customerSession->unsAllowedResource();
        return $this;
    }

}
