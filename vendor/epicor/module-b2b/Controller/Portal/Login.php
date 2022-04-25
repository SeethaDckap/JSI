<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Portal;

class Login extends \Epicor\B2b\Controller\Portal
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        \Epicor\B2b\Controller\Context $context,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Framework\Translate\Inline\StateInterface $translateInterface,
        \Magento\Email\Model\TemplateFactory $emailTemplateFactory,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Model\Message\Request\CncFactory $commMessageRequestCncFactory,
        \Magento\Customer\Model\FormFactory $customerFormFactory,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $state,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $directoryRegionFactory, $translateInterface, $emailTemplateFactory, $commCustomerErpaccountFactory, $customerCustomerFactory, $commMessageRequestCncFactory, $customerFormFactory, $customerAddressFactory,  $cache, $state, $customerUrl, $subscriberFactory);
    }


    /**
     * Customer login form page
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn()) {
            if ($this->getRequest()->getParam('access') != 'denied') {
                $this->_redirect($this->_getSuccessUrl());
                return;
            }
        }
        if (!$this->_scopeConfig->isSetFlag('customer/startup/redirect_dashboard')
            && $this->_scopeConfig->getValue('epicor_common/login/landing_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 'last_page') {
            $this->_getSession()->unsNoReferer(false);
        }
        return $this->processlayout();
    }

}
