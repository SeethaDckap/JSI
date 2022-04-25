<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Portal;

class RegisterPost extends \Epicor\B2b\Controller\Portal
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

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
    )
    {

        $this->customerconnectHelper = $context->getCustomerConnectHelper();
        parent::__construct($context, $directoryRegionFactory, $translateInterface, $emailTemplateFactory, $commCustomerErpaccountFactory, $customerCustomerFactory, $commMessageRequestCncFactory, $customerFormFactory, $customerAddressFactory,  $cache, $state, $customerUrl, $subscriberFactory);
    }

    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $delivery = $this->getRequest()->getPost('delivery');
            if ($delivery) {
                $this->customerconnectHelper->addressValidate($delivery);
            }
            $registered = $this->getRequest()->getPost('registered');
            if ($registered) {
                $this->customerconnectHelper->addressValidate($registered);
            }
            $invoice = $this->getRequest()->getPost('registered');
            if ($invoice) {
                $this->customerconnectHelper->addressValidate($invoice);
            }

            $option = $this->scopeConfig->getValue('epicor_b2b/registration/b2b_acct_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $preRegOption = $this->scopeConfig->getValue('epicor_b2b/registration/pre_reg_pswd' ,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $preRegEnabled = $preRegOption == 1 || $preRegOption == 2;
            $b2bcompanyreg = $this->getRequest()->getPost('b2bcompanyreg');

            if (!empty($b2bcompanyreg) && $preRegEnabled) {
                $return = $this->_registrationGroupPassword($preRegOption);
            } else {
                switch ($option) {
                    case 'no_acct':
                        $this->_registrationRequestEmail();
                        break;
                    case 'guest_acct':
                        $return = $this->_registrationCashEmail();
                        break;
                    case 'erp_acct':
                        $return = $this->_registrationSendCnc(0);
                        break;
                    case 'erp_acct_email':
                        $return = $this->_registrationSendCnc(1);
                        break;
                    default:
                        $return = false;
                }
            }
        }

        //redirect to selected home page after successful registration (user will be logged in)
        if ($this->_getSession()->isLoggedIn()) {
            $successRedirection = $this->getB2bNewAccountSuccessUrl();
            $this->_addSuccesMessage();
            if ($successRedirection != '') {
                $this->_redirect($successRedirection);
            }
        }
    }

}
