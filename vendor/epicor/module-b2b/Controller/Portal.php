<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller;

use Magento\Framework\Exception\InputException;
use Magento\Store\Model\ScopeInterface;

abstract class Portal extends \Magento\Framework\App\Action\Action
{

    const DEFAULT_ACCOUNT_SUCCESS_URL = 'customer/account';

    protected $successMessageDisplayed = false;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * // @var \Magento\Framework\TranslateInterface
     *  Magento\Framework\Translate\Inline\StateInterface $translateInterface,
     */
    protected $translateInterface;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $emailTemplateFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\CncFactory
     */
    protected $commMessageRequestCncFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\FormFactory
     */
    protected $customerFormFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPage;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
        /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

     /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectHelper;
    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;
     /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    protected $_isConfirmation = 0;

    /**
     * @var \Epicor\B2b\Model\ResourceModel\User
     */
    protected $user;

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
        $this->customerSession = $context->getCustomerSession();
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->translateInterface = $translateInterface;
        $this->storeManager = $context->getStoreManager();
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commMessageRequestCncFactory = $commMessageRequestCncFactory;
        $this->registry = $context->getRegistry();
        $this->customerFormFactory = $customerFormFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->eventManager = $context->getEventManager();
        $this->cache = $cache;
        $this->_cacheState = $state;
        $this->customerUrl = $customerUrl;
        $this->resultPage = $context->getResultPageFactory();
        $this->transportBuilder = $context->getTransportBuilder();
        $this->customerconnectMessageRequestCuau=$context->getCustomerconnectMessageRequestCuau();
        $this->customerconnectHelper=$context->getCustomerConnectHelper();
        $this->commonXmlHelper = $context->getCommonXmlHelper();
        $this->localeResolver= $context->getLocaleResolver();
        $this->customerRepositoryInterface = $context->getCustomerRepositoryInterface();
        $this->productMetadata = $context->getProductMetadata();
        $this->subscriberFactory = $subscriberFactory;
        $this->user = $context->getUserResourceModel();
        parent::__construct(
            $context
        );
    }


    /**
     * Retrieve customer session model object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    protected function _getRegUrl($isPreReg = 0)
    {
        return 'b2b/portal/register/prereg/'.$isPreReg.'/';
    }

    protected function _getLoginUrl()
    {
        return 'b2b/portal/login';
    }

    protected function _getSuccessUrl()
    {
        return '/';
    }

    protected function _getData()
    {
        return $this->getRequest()->getPost();
    }

    protected function _sendEmail($templateId, $vars, $to, $name, $from = null)
    {
        try {
            if (isset($vars['region_id'])) {
                if ($vars['region_id'] != '') {
                    $vars['state_code'] = $this->directoryRegionFactory->create()->load($vars['region_id'])->getName();
                } else {
                    $vars['state_code'] = $vars['region'];
                }
            }
            $translate = $this->translateInterface;
            /* @var $translate \Magento\Framework\Translate\Inline\StateInterface */
            $translate->suspend(false);

            $storeId = $this->storeManager->getStore()->getId();
            if(is_null($from)){
                $from = $this->scopeConfig->getValue('customer/create_account/email_identity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            }
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
                ->setTemplateVars($vars)
                ->setFrom($from)
                ->addTo($to, $name)
                ->getTransport();

            $transport->sendMessage();
            $translate->resume(true);

        } catch (\Exception $e) {
            $translate->resume(true);
        }

    }

    protected function _registrationRequestEmail()
    {
        $customerData = $this->_getData()->toArray();
        $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/no_acct_admin_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $setting = $this->scopeConfig->getValue('epicor_b2b/registration/no_acct_admin_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $email = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $name = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $successMsg = $this->scopeConfig->getValue('epicor_b2b/registration/email_request_success_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customerData['adminname'] = $name;
        $customerData['customer_email'] = $customerData['email'];
        $customerData['street1'] = @$customerData['street'][0];
        $customerData['street2'] = @$customerData['street'][1];
        $customerData['street3'] = @$customerData['street'][2];
        $this->_sendEmail($templateId,$customerData, $email, $name, $setting);

        $this->messageManager->addSuccess($successMsg);
        $this->_redirect($this->_getLoginUrl());
    }

    protected function _registrationCashEmail()
    {
        if ($this->_createAccount()) {
            //send email requesting account.
            $customerData = $this->_getData()->toArray();
            $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/guest_acct_admin_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $setting = $this->scopeConfig->getValue('epicor_b2b/registration/guest_acct_admin_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $email = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $name = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $customerData['adminname'] = $name;
            $customerData['customer_email'] = $customerData['email'];
            $customerData['street1'] = @$customerData['street'][0];
            $customerData['street2'] = @$customerData['street'][1];
            $customerData['street3'] = @$customerData['street'][2];
            $this->_sendEmail($templateId, $customerData, $email, $name, $setting);
            $sendWelcomeEmail = $this->scopeConfig->isSetFlag('epicor_b2b/registration/enable_guest_acct_welcome_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $requireConfirm = $this->scopeConfig->isSetFlag('customer/create_account/confirm', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($sendWelcomeEmail && !$requireConfirm) {
                //send welcome email
                $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/guest_acct_welcome_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $customerData['customer_email'] = $customerData['email'];
                $customerData['fullname'] = $customerData['firstname']." ".$customerData['lastname'];
                $this->_sendEmail($templateId, $customerData, $customerData['email'], $customerData['fullname']);
            }
            $this->_redirect($this->_getSuccessUrl());
        } else {
            $this->_redirect($this->_getRegUrl());
        }
    }

    protected function _registrationGroupPassword($preRegOption)
    {
        $error = '';
        //find customer group with this password.
        $customerData = $this->_getData();
        $erpAccount = $this->commCustomerErpaccountFactory->create()
            ->load($customerData['b2bcompanyreg'], 'pre_reg_password');
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        if (!empty($erpAccount) && !$erpAccount->isEmpty()) {
            if ($erpAccount->isValidForStore()) {
                if ($this->_createAccount($erpAccount, true)) {
                    $customerData = $this->_getData()->toArray();
                    if($preRegOption === "2"){
                        //send admin email
                        $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/pre_reg_admin_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $setting = $this->scopeConfig->getValue('epicor_b2b/registration/pre_reg_admin_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $email = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $name = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $customerData['company'] = $erpAccount->getName();
                        $customerData['adminname'] = $name;
                        $customerData['customer_email'] = $customerData['email'];
                        $this->_sendEmail($templateId, $customerData, $email, $name, $setting);
                    }
                    $sendWelcomeEmail = $this->scopeConfig->isSetFlag('epicor_b2b/registration/enable_pre_reg_welcome_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $requireConfirm = $this->scopeConfig->isSetFlag('customer/create_account/confirm', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    if($sendWelcomeEmail && !$requireConfirm) {
                        //send welcome email
                        $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/pre_reg_welcome_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $customerData['customer_email'] = $customerData['email'];
                        $customerData['fullname'] = $customerData['firstname']." ".$customerData['lastname'];
                        $this->_sendEmail($templateId, $customerData, $customerData['email'], $customerData['fullname']);
                    }
                    $this->_redirect($this->_getSuccessUrl());
                } else {
                    $error = 'Error occured while creating account';
                }
            } else {
                $error = 'Pre-Registered Password is not valid for this website';
            }
        } else {
            $error = 'Unknown Pre register password';
        }

        if ($error) {
            if (empty($this->messageManager->getMessages()->getItems())) {
                $this->messageManager->addErrorMessage($error);
            }
            $this->_redirect($this->_getRegUrl(1));
        }
    }

    protected function _registrationSendCnc($sendEmail)
    {
        //find customer group with this password.
        $customerData = $this->_getData()->toArray();

        $error = '';

        if ($customerData) {
            try {

                // set registered delivery and invoice email
                $customerData['registered']['email_address'] = $customerData['email'];
                $customerData['delivery']['email_address'] = $customerData['email'];
                $customerData['invoice']['email_address'] = $customerData['email'];
                $customer = $this->customerCustomerFactory->create();
                /* @var $customer \Magento\Customer\Model\Customer */

                $customer->setStore($this->storeManager->getStore());
                $customer->setWebsiteId($this->storeManager->getWebsite()->getId());

                $customer->loadByEmail($customerData['email']);
                if (!$customer->getId()) {
                    $cnc = $this->commMessageRequestCncFactory->create();
                    /* @var $cnc \Epicor\Comm\Model\Message\Request\Cnc */
                    if ($cnc->isActive()) {
                        $erpAccount = $this->commCustomerErpaccountFactory->create();
                        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
                        $erpAccount->setName($customerData['company']);
                        $erpAccount->setEmail($customerData['email']);

                        $registeredAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/registered_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $invoiceAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/invoice_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $deliveryAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/delivery_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                        if ($registeredAddress)
                            $erpAccount->addAddress('registered', 'registered', $customerData['registered']);
                        if ($deliveryAddress)
                            $erpAccount->addAddress('delivery', 'delivery', $customerData['delivery']);
                        if ($invoiceAddress)
                            $erpAccount->addAddress('invoice', 'invoice', $customerData['invoice']);

                        //set account type
                        $erpAccount->setAccountType($customerData['acct_type']);
                        $erpAccount->setTemplateCodePath('default_template_code');

                        $cnc->setAccount($erpAccount);
                        if ($cnc->sendMessage()) {
                            if ($this->_createAccount($cnc->getAccount())) {
                                if(!$this->_isConfirmation){
                                    $this->_addSuccesMessage();
                                }
                            } else {
                                $error = __('Error occured while creating account');
                            }
                        } else {
                            //M1 > M2 Translation Begin (Rule 55)
                            //$error = $this->__('Account creation failed. Error - %s', $cnc->getStatusDescriptionText());
                            $error = __('Account creation failed. Error - %1', $cnc->getStatusDescriptionText());
                            //M1 > M2 Translation End
                        }
                    } else {
                        $error = __('Account creation failed. Messaging Disabled');
                    }
                } else {
                    //M1 > M2 Translation Begin (Rule p2-4)
                    //$url = Mage::getUrl('customer/account/forgotpassword');
                    $url = $this->_url->getUrl('customer/account/forgotpassword');
                    //M1 > M2 Translation End
                    //M1 > M2 Translation Begin (Rule 55)
                    //$error = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    $this->messageManager->addComplexErrorMessage(
                        'b2bCustomerErrorMessage',
                            [
                                'message_start' =>'There is already an account with this email address. If you are sure that it is your email address,',
                                'url' => $url,
                                'message_end' =>'to get your password and access your account.',
                                'magento_version'=> $this->productMetadata->getVersion()
                            ]
                        );

                    //M1 > M2 Translation End
                    //$this->_getSession()->setEscapeMessages(false);
                    $this->_redirect('b2b/portal/register/prereg/1/');
                    return;
                }
            } catch (\Exception $e) {
                //M1 > M2 Translation Begin (Rule 55)
                //$error = $this->__('ERP Account creation failed. Error  - %s', $e->getMessage());
                $error = __('ERP Account creation failed. Error  - %1', $e->getMessage());
                //M1 > M2 Translation End
            }
        } else {
            $error = __('No data found to save');
        }

        if ($error) {
            $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
            $this->messageManager->addErrorMessage($error);
            $this->_redirect($this->_getRegUrl());
        } else {
            if($sendEmail){
                //send admin email
                $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/erp_acct_admin_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $setting = $this->scopeConfig->getValue('epicor_b2b/registration/erp_acct_admin_email_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $email = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $name = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $customerData['adminname'] = $name;
                $customerData['customer_email'] = $customerData['email'];
                $this->_sendEmail($templateId, $customerData, $email, $name, $setting);
            }

            $sendWelcomeEmail = $this->scopeConfig->isSetFlag('epicor_b2b/registration/enable_erp_acct_welcome_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $requireConfirm = $this->scopeConfig->isSetFlag('customer/create_account/confirm', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($sendWelcomeEmail && !$requireConfirm) {
                //send welcome email
                $accountName = $customerData['company'];
                $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/erp_acct_welcome_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $customerData['customer_email'] = $customerData['email'];
                $customerData['fullname'] = $customerData['firstname']." ".$customerData['lastname'];
                $customerData['account_name'] = $accountName;
                $this->_sendEmail($templateId, $customerData, $customerData['email'], $customerData['fullname']);
            }

            $this->_redirect($this->_getSuccessUrl());
        }
    }

    /**
     * @return mixed|string
     */
    public function getB2bNewAccountSuccessUrl()
    {
        $successRedirection = self::DEFAULT_ACCOUNT_SUCCESS_URL;

        if ($url =  $this->scopeConfig->getValue(
            'epicor_b2b/registration/success_redirection',
            ScopeInterface::SCOPE_STORE
        )) {
            if(strpos($url, '|') !== false) {
                $newvalue = explode('|', $url);
                return $successRedirection = $newvalue[0];
            }
            return $successRedirection = $url;
        }

        return $successRedirection;
    }

    /**
     * Create customer account action
     *
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     */
    protected function _createAccount($erpAccount = false, $isPreReg = false)
    {
        $session = $this->_getSession();
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if ($this->getRequest()->isPost()) {
            $errors = array();

            if (!$customer = $this->registry->registry('current_customer')) {
                $customer = $this->customerCustomerFactory->create()->setId(null);
            }

            /* @var $customerForm \Magento\Customer\Model\Form */
            $customerForm = $this->customerFormFactory->create();
            $customerForm->setFormCode('customer_account_create')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            /**
             * Initialize customer group id
             */
            if ($erpAccount !== false) {
                if ($erpAccount->isTypeSupplier()) {
                    $customer->setEccSupplierErpaccountId($erpAccount->getId());
                } else {
                    $customer->setData('ecc_erpaccount_id', $erpAccount->getId());
                }
            }

            $customer->setForceErpAccountGroup(1);
        }
        if ($this->getRequest()->getPost('create_address')) {
            /* @var $address \Magento\Customer\Model\Address */
            $address = $this->customerAddressFactory->create();
            /* @var $addressForm \Magento\Customer\Model\Form */
            $addressForm = $this->customerFormFactory->create();
            $addressForm->setFormCode('customer_register_address')
                ->setEntity($address);

            $addressData = $addressForm->extractData($this->getRequest(), 'address', false);
            $addressErrors = $addressForm->validateData($addressData);
            if ($addressErrors === true) {
                $address->setId(null)
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                $addressForm->compactData($addressData);
                $customer->addAddress($address);

                $addressErrors = $address->validate();
                if (is_array($addressErrors)) {
                    $errors = array_merge($errors, $addressErrors);
                }
            } else {
                $errors = array_merge($errors, $addressErrors);
            }
        }

        try {
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);

				$cpassword = $this->getRequest()->getPost('password');
                $cconfirmation = $this->getRequest()->getPost('confirmation');
                $this->checkForSimplePassword($cpassword);

                if ($cpassword != null) {
                    $customer->setPassword($cpassword);
                }
                if ($cconfirmation != null) {
                    $customer->setConfirmation($cconfirmation);
                    $customer->setPasswordConfirmation($cconfirmation);
                }

                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($customerErrors, $errors);
                }
            }

            $validationResult = count($errors) == 0;

            if (true === $validationResult) {
                $customer->save();
                if ($cpassword != null) {
                    $passwordHash = $customer->getPasswordHash();
                    $this->user->trackPassword($customer, $passwordHash);
                }
                $customer->setCustomerIsNew(true);
                $customer->setNewCustomerSuccessUrl($this->getB2bNewAccountSuccessUrl());
                $customerRepository = $this->customerRepositoryInterface->getById($customer->getId());

                $this->eventManager->dispatch('customer_register_success', array('account_controller' => $this, 'customer' => $customer)
                );

                if ($customer->isConfirmationRequired()) {
                    $customer->sendNewAccountEmail(
                        'confirmation', $session->getBeforeAuthUrl(), $this->storeManager->getStore()->getId()
                    );
                    $this->_isConfirmation = 1;
                    if($erpAccount !== false){
                        $extensionAttributes = $customerRepository->getExtensionAttributes(); /** get current extension attributes from entity **/
                        $extensionAttributes->setEccMultiErpId($erpAccount->getId());
                        $extensionAttributes->setEccMultiContactCode($customer->getEccContactCode());
                        $extensionAttributes->setEccMultiErpType('customer');
                        $customerRepository->setExtensionAttributes($extensionAttributes);
                    }
                    $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
                    $this->customerRepositoryInterface->save($customerRepository);
                    //M1 > M2 Translation Begin (Rule 55)
                    //$session->addSuccess($this->scopeConfig->getValue('epicor_b2b/registration/success_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                    //M1 > M2 Translation Begin (Rule 58)
                    //$session->addSuccess($this->scopeConfig->getValue('epicor_b2b/registration/success_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) . $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                    $this->messageManager->addSuccess( __(' Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.', $this->customerUrl->getEmailConfirmationUrl($customer->getEmail())));
                    //M1 > M2 Translation End
                    //M1 > M2 Translation End
                } else {
                    $cucoPending = 0;
                    // Work Around for customer custom attribute data reset when customer saved again
                    //$customerRepository = $this->customerRepositoryInterface->getById($customer->getId());
                    if($erpAccount !== false){
//                        $customerRepository->setCustomAttribute('ecc_erpaccount_id', $erpAccount->getId());
//                        $customerRepository->setCustomAttribute('ecc_erp_account_type', 'customer');
//                        $customerRepository->setCustomAttribute('ecc_contact_code', $customer->getEccContactCode());
                        $extensionAttributes = $customerRepository->getExtensionAttributes(); /** get current extension attributes from entity **/
                        $extensionAttributes->setEccMultiErpId($erpAccount->getId());
                        $extensionAttributes->setEccMultiContactCode($customer->getEccContactCode());
                        $extensionAttributes->setEccMultiErpType('customer');
                        $customerRepository->setExtensionAttributes($extensionAttributes);
                    }else{
                        $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
                        $cucoPending = 1;
                    }
                    // Now customer custom attribute data have been reste due to issue so save it again using Repository
                    $this->customerRepositoryInterface->save($customerRepository);
                    // end work around
                    $session->setCustomerAsLoggedIn($customer);
                    if($cucoPending){
                        $customerRepository->setCustomAttribute('ecc_cuco_pending', $cucoPending);
                        $this->customerRepositoryInterface->save($customerRepository);
                    }

                    // display success message on first entry after account created
                    $this->_addSuccesMessage();
                }
                //send CUAU to create the login in ERP.
                $option = $this->scopeConfig->getValue('epicor_b2b/registration/b2b_acct_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($isPreReg || (($option === 'erp_acct' || $option === 'erp_acct_email') && $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/cnc_request/send_automate_cnc_request', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))) {
                    if ($this->getRequest()->getPost('firstname') && $this->getRequest()->getPost('lastname')) {
                        $name = $this->getRequest()->getPost('firstname') . ' ' . $this->getRequest()->getPost('lastname');
                    } else {
                        $name = $this->getRequest()->getPost('firstname');
                    }
                    $registeredData = $this->getRequest()->getPost('registered');
                    $formData = array('login_id' => 'true',
                        'name' => $name,
                        'telephone_number' => isset($registeredData['phone']) ? $registeredData['phone'] : '',
                        'fax_number' => isset($registeredData['fax_number']) ? $registeredData['fax_number'] : '',
                        'email_address' => $this->getRequest()->getPost('email'),
                        'function'=>null,
                    );
                    $oldFormData = false;
                    $message = $this->customerconnectMessageRequestCuau;
                    $message->addContact('A', $formData, $oldFormData);
                    $this->sendUpdate($message, $customer->getEccErpaccountId(), $customerRepository);
                }
                if ($this->getRequest()->getParam('is_subscribed', false)) {
                    $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
                }
                $this->eventManager->dispatch('ecc_business_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );
                return true;
            } else {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if (is_array($errors)) {
                    foreach ($errors as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Invalid customer data'));
                }
            }
        } catch (\Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === \Magento\Framework\Exception\InvalidEmailOrPasswordException::INVALID_EMAIL_OR_PASSWORD) {
                //M1 > M2 Translation Begin (Rule p2-4)
                //$url = Mage::getUrl('customer/account/forgotpassword');
                $url = $this->_url->getUrl('customer/account/forgotpassword');
                //M1 > M2 Translation End
                //M1 > M2 Translation Begin (Rule 55)
                //$message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                $message = __('There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.', $url);
                //M1 > M2 Translation End
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $this->messageManager->addErrorMessage($message);
        } catch (\Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost())
                ->addException($e, __('Cannot save the customer.'));
        }
        return false;
    }

    protected function processlayout()
    {
        $resultPage = $this->resultPage->create();

        if ($this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portaltype', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $resultPage->getConfig()->setPageLayout('1column');
            $customHandle = 'use_portal';
        } else {
            $resultPage->getConfig()->setPageLayout('1column');
            $customHandle = 'use_one_column';
        }
        $update = $resultPage->getLayout()->getUpdate();
        $resultPage->addHandle($customHandle);
        //M1 > M2 Translation Begin (Rule 12)
        /*if (Mage::app()->useCache('layout')) {
            $cacheId = $update->getCacheId() . $customHandle;
            $update->setCacheId($cacheId);

            if (!$this->cache->load($cacheId)) {
                foreach ($update->getHandles() as $handle) {
                    $update->merge($handle);
                }
                $update->saveCache();
            } else {
                //load updates from cache
                $update->load();
            }
        } else {
        //load updates
            $update->load();
        }*/
        if ($this->_cacheState->isEnabled('layout')) {
            $cacheId = $update->getCacheId() . $customHandle;
            //$update->setCacheId($cacheId);

            if (!$this->cache->load($cacheId)) {
                foreach ($update->getHandles() as $handle) {
                    $update->load($handle);
                }
                //$update->saveCache();
            } else {
                $update->load();
            }
        } else {
            $update->load();
        }
        //M1 > M2 Translation End

        $this->_view->generateLayoutXml()->generateLayoutBlocks();
        //M1 > M2 Translation Begin (Rule 13)
        //$this->_initLayoutMessages('customer/session');
        //$this->_initLayoutMessages('catalog/session');
        //M1 > M2 Translation End
        return $resultPage;
    }
    /**
     * Add the success Account Registration Message if it hasn't been added already
     */
    protected function _addSuccesMessage()
    {
        if ($this->successMessageDisplayed == false) {
            $successMessage = $this->scopeConfig->getValue('epicor_b2b/registration/success_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!empty($successMessage)) {
                $this->messageManager->addSuccessMessage(__($this->scopeConfig->getValue('epicor_b2b/registration/success_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
            }
        }
        $this->successMessageDisplayed = true;
    }

    protected function sendUpdate($message, $erpAccountId, $customerRepository)
    {
        $helper = $this->customerconnectHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */
        $xmlHelper = $this->commonXmlHelper;
        /* @var $helper Epicor_Common_Helper_Xml */
        $storeId = $this->storeManager->getStore()->getId();
        $erp_account_number = $helper->getErpAccountNumber($erpAccountId, $storeId);
        $messageTypeCheck = $message->getHelper()->getMessageType('CUAU');

        if ($message->isActive() && $messageTypeCheck) {

            $message->setAccountNumber($erp_account_number)
                ->setLanguageCode($helper->getLanguageMapping($this->localeResolver->getLocale()));
            if ($message->sendMessage()) {

                $response = $message->getResponse();
                $contacts = $xmlHelper->varienToArray($response->getCustomer()->getContacts());
                $currentEmail = $customerRepository->getEmail();

                if (isset($contacts['contact']['email_address'])) {
                    if($currentEmail === $contacts['contact']['email_address']){
                        $filteredContact[0] = $contacts['contact'];
                    }
                } else {
                    $filteredContact = array_values(array_filter($contacts['contact'], function($arrayValue) use($currentEmail) {
                        return $arrayValue['email_address'] == $currentEmail;
                    }));
                }

                if (empty($filteredContact[0]['contact_code'])) {
                    $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
                } else {
                    //  $customerRepository->setCustomAttribute('ecc_contact_code', $filteredContact[0]['contact_code']);
                    $extensionAttributes = $customerRepository->getExtensionAttributes(); /** get current extension attributes from entity **/
                    $extensionAttributes->setEccMultiContactCode($filteredContact[0]['contact_code']);
                    $customerRepository->setExtensionAttributes($extensionAttributes);
                    $customerRepository->setCustomAttribute('ecc_cuco_pending', '0');
                }
            } else {
                $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
            }
        } else {
            $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
        }
        $this->customerRepositoryInterface->save($customerRepository);
    }

    /**
     * Check if the password is from weak password dictionary
     * @param string $password
     * @return bool
     */
    private function checkForSimplePassword($password)
    {
        $simplePassList = $this->user->getWeakPasswords($password);
        if (empty($simplePassList) === false) {
            throw new InputException(
                __(
                    'Sorry, but this password is weak. Please create another.'
                )
            );
        }
        return true;
    }

}
