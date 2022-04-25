<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer;

use Magento\Framework\Event\Observer;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $listsFrontendContractHelper;

    protected $customerSession;

    protected $commHelper;

    protected $commonAccessHelper;

    protected $scopeConfig;

    public function __construct(
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->scopeConfig = $scopeConfig;
    }

}

