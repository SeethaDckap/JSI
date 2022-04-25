<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Portal;

class Register extends \Epicor\B2b\Controller\Portal
{

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
        parent::__construct($context, $directoryRegionFactory, $translateInterface, $emailTemplateFactory, $commCustomerErpaccountFactory, $customerCustomerFactory, $commMessageRequestCncFactory, $customerFormFactory, $customerAddressFactory,  $cache, $state, $customerUrl, $subscriberFactory);
    }


    public function execute()
    {
        //Access controls for B2B portal login
        $portalEnabled = $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $preRegOption = $this->scopeConfig->getValue('epicor_b2b/registration/pre_reg_pswd' ,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $acctOption = $this->scopeConfig->getValue('epicor_b2b/registration/b2b_acct_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $urlParam = $this->_request->getParams('prereg') ? $this->_request->getParams('prereg')['prereg'] : 0;

        if($urlParam == 1 && ($preRegOption == 0 || !$portalEnabled)){
            $this->messageManager->addErrorMessage(__('Pre-Registered Password is not valid for this website'));
            $this->_redirect($this->_getLoginUrl());
            return;
        }else if($urlParam ==0  && (!$portalEnabled || $acctOption === 'disable_new_erp_acct')){
            $this->messageManager->addErrorMessage(__('Business Account Creation is not valid for this website'));
            $this->_redirect($this->_getLoginUrl());
            return;
        }else if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect($this->_getSuccessUrl());
            return;
        }

        return $this->processlayout();
    }

    }
