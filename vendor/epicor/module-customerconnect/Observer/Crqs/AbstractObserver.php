<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Observer\Crqs;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface {
    
    /*
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /*
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
    \Epicor\Dealerconnect\Helper\Data $dealerHelper, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Registry $registry, \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->dealerHelper = $dealerHelper;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->commHelper = $commHelper;

    }

}
