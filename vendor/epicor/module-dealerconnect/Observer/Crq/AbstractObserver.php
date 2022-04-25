<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Observer\Crq;

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

    public function __construct(
    \Epicor\Dealerconnect\Helper\Data $dealerHelper, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Registry $registry
    ) {
        $this->dealerHelper = $dealerHelper;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
    }

}
