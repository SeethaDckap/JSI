<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Observer\Cuod;

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
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
    \Epicor\Dealerconnect\Helper\Data $dealerHelper, \Magento\Customer\Model\Session $customerSession, \Epicor\AccessRight\Model\Authorization $_accessauthorization, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->dealerHelper = $dealerHelper;
        $this->customerSession = $customerSession;
        $this->_accessauthorization = $_accessauthorization;
        $this->scopeConfig = $scopeConfig;
    }

}
