<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;

/**
 * note: Becuase of compile issue m2.3.3 Class item
 * move from preference to plugin
 *
 * @package Epicor\Common\Plugin
 */
class CreatePost
{
    const DEFAULT_ACCOUNT_SUCCESS_URL = 'customer/account';

    /**
     * @var bool
     */
    protected $successMessageDisplayed = false;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_responce;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $translateInterface;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\CncFactory
     */
    protected $commMessageRequestCncFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Customer\Model\FormFactory
     */
    protected $customerFormFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $_isConfirmation = 0;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        SubscriberFactory $subscriberFactory,
        CustomerRepository $customerRepository,
        CustomerUrl $customerUrl,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Framework\Translate\Inline\StateInterface $translateInterface,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Model\Message\Request\CncFactory $commMessageRequestCncFactory,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Customer\Model\FormFactory $customerFormFactory,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Common\Helper\Xml $commonXmlHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->eventManager = $context->getEventManager();
        $this->_redirect = $context->getRedirect();
        $this->_responce = $context->getResponse();
        $this->messageManager = $context->getMessageManager();
        $this->_request = $context->getRequest();
        $this->session = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->customerUrl = $customerUrl;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->translateInterface = $translateInterface;
        $this->transportBuilder = $transportBuilder;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commMessageRequestCncFactory = $commMessageRequestCncFactory;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->productMetadata = $productMetadata;
        $this->customerFormFactory = $customerFormFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->customerconnectMessageRequestCuau = $customerconnectMessageRequestCuau;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_localeResolver = $localeResolver;
        $this->commonXmlHelper = $commonXmlHelper;
        $this->_url = $context->getUrl();
        $this->registry = $registry;
    }

    /**
     * Get all types to extensions map including log files extensions
     *
     * @return array
     */
    public function aroundExecute(\Epicor\Common\Controller\Account\CreatePost $subject, \Closure $proceed)
    {
        $customerData = $this->getRequest()->getPost()->toArray();
        if (!isset($customerData['email'])) {
            $result = $proceed();
            return $result;
        }
        $option = $this->scopeConfig->getValue('epicor_b2b/registration/b2c_acct_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        switch ($option) {
            case 'guest_acct':
            case 'guest_acct_email':
                $return = $proceed();
                break;
            case 'erp_acct':
                $return = $this->_customerSendCnc(0);
                break;
            case 'erp_acct_email':
                $return = $this->_customerSendCnc(1);
                break;
            default:
                $return = false;
        }

        return $this->accountAction($option, $return);
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }


    protected function _preGuestEmail()
    {
        $customerData = $this->getRequest()->getPost()->toArray();
        $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/b2c_guest_acct_admin_email_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $setting = $this->scopeConfig->getValue('epicor_b2b/registration/b2c_guest_acct_admin_email_address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $email = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $name = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $customerData['adminname'] = $name;
        $customerData['customer_email'] = $customerData['email'];
        $customerData['street1'] = @$customerData['street'][0];
        $customerData['street2'] = @$customerData['street'][1];
        $customerData['street3'] = @$customerData['street'][2];
        $this->_sendEmail($templateId, $customerData, $email, $name, $setting);
    }

    /**
     * @param $templateId
     * @param $vars
     * @param $to
     * @param $name
     * @param null $from
     */
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
            if (is_null($from)) {
                $from = $this->scopeConfig->getValue('customer/create_account/email_identity',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

    /**
     * @param $action
     * @param $return
     * @return mixed
     */
    protected function accountAction($action, $return)
    {
        if ($action === 'guest_acct_email' || $action === 'guest_acct') {
            $customerData = $this->getRequest()->getPost()->toArray();
            $email = isset($customerData['email']) ? $customerData['email'] : '';

            if ($this->getRequest()->getParam('is_subscribed', false) && $email) {
                $this->subscriberFactory->create()->subscribe($email);
            }
        }
        if ($action === 'guest_acct_email') {
            $this->_preGuestEmail();
        }
        return $return;
    }

    /**
     * @param int $sendEmail
     */
    protected function _customerSendCnc($sendEmail = 0)
    {
        //find customer group with this password.
        $customerData = $this->getRequest()->getPost()->toArray();
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
                        $erpAccount->setName($customerData['firstname'] . $customerData['lastname']);
                        $erpAccount->setEmail($customerData['email']);

                        $registeredAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_registered_address',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $invoiceAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_invoice_address',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $deliveryAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_delivery_address',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                        if ($registeredAddress) {
                            $erpAccount->addAddress('registered', 'registered', $customerData['registered']);
                        }
                        if ($deliveryAddress) {
                            $erpAccount->addAddress('delivery', 'delivery', $customerData['delivery']);
                        }
                        if ($invoiceAddress) {
                            $erpAccount->addAddress('invoice', 'invoice', $customerData['invoice']);
                        }

                        //set account type
                        $erpAccount->setAccountType('C');
                        $erpAccount->setTemplateCodePath('b2c_default_template_code');

                        $cnc->setAccount($erpAccount);
                        if ($cnc->sendMessage()) {
                            if ($this->_createAccount($cnc->getAccount())) {
                                if (!$this->_isConfirmation) {
                                    $this->_addSuccesMessage();
                                }
                            } else {
                                $error = __('Error occured while creating account');
                            }
                        } else {
                            $error = __('Account creation failed. Error - %1', $cnc->getStatusDescriptionText());
                        }
                    } else {
                        $error = __('Account creation failed. Messaging Disabled');
                    }
                } else {
                    $url = $this->_url->getUrl('customer/account/forgotpassword');
                    $this->messageManager->addComplexErrorMessage(
                        'b2bCustomerErrorMessage',
                        [
                            'message_start' => 'There is already an account with this email address. If you are sure that it is your email address,',
                            'url' => $url,
                            'message_end' => 'to get your password and access your account.',
                            'magento_version' => $this->productMetadata->getVersion()
                        ]
                    );

                    $this->_redirect->redirect($this->_responce,'customer/account/create/');
                    return;
                }
            } catch (\Exception $e) {
                $error = __('ERP Account creation failed. Error  - %1', $e->getMessage());
            }
        } else {
            $error = __('No data found to save');
        }

        if ($error) {
            $this->session->setCustomerFormData($this->getRequest()->getPost()->toArray());
            $this->messageManager->addErrorMessage($error);
            $this->_redirect->redirect($this->_responce,$this->_getRegUrl());
        } else {
            //send admin email
            if ($sendEmail) {
                $templateId = $this->scopeConfig->getValue('epicor_b2b/registration/b2c_erp_acct_admin_email_template',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $setting = $this->scopeConfig->getValue('epicor_b2b/registration/b2c_erp_acct_admin_email_address',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $email = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $name = $this->scopeConfig->getValue('trans_email/ident_' . $setting . '/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $customerData['adminname'] = $name;
                $customerData['customer_email'] = $customerData['email'];
                $this->_sendEmail($templateId, $customerData, $email, $name, $setting);
            }

            $this->_redirect->redirect($this->_responce, $this->_getSuccessUrl());
        }
    }

    /**
     * @param bool $erpAccount
     * @return bool
     */
    protected function _createAccount($erpAccount = false)
    {
        $session = $this->session;
        /* @var $session Session */
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

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

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
                $customer->setPassword($this->getRequest()->getPost('password'));
                $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
                $customer->setPasswordConfirmation($this->getRequest()->getPost('confirmation'));
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($customerErrors, $errors);
                }
            }

            $validationResult = count($errors) == 0;

            if (true === $validationResult) {
                $customer->save();
                $customer->setCustomerIsNew(true);
                $customer->setNewCustomerSuccessUrl($this->getB2bNewAccountSuccessUrl());
                $customerRepository = $this->customerRepository->getById($customer->getId());

                $this->eventManager->dispatch('customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );

                if ($customer->isConfirmationRequired()) {
                    $customer->sendNewAccountEmail(
                        'confirmation', $session->getBeforeAuthUrl(), $this->storeManager->getStore()->getId()
                    );
                    $this->_isConfirmation = 1;
                    if ($erpAccount !== false) {
//                        $customerRepository->setCustomAttribute('ecc_erpaccount_id', $erpAccount->getId());
//                        $customerRepository->setCustomAttribute('ecc_erp_account_type', 'customer');
//                        $customerRepository->setCustomAttribute('ecc_contact_code', $customer->getEccContactCode());
                        $extensionAttributes = $customerRepository->getExtensionAttributes();
                        /** get current extension attributes from entity **/
                        $extensionAttributes->setEccMultiErpId($erpAccount->getId());
                        $extensionAttributes->setEccMultiContactCode($customer->getEccContactCode());
                        $extensionAttributes->setEccMultiErpType('customer');
                        $customerRepository->setExtensionAttributes($extensionAttributes);
                        $this->customerRepository->save($customerRepository);
                    }
                    $this->messageManager->addSuccess(__(' Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%1">click here</a>.',
                        $this->customerUrl->getEmailConfirmationUrl($customer->getEmail())));

                } else {
                    // Work Around for customer custom attribute data reset when customer saved again
                    if ($erpAccount !== false) {
//                        $customerRepository->setCustomAttribute('ecc_erpaccount_id', $erpAccount->getId());
//                        $customerRepository->setCustomAttribute('ecc_erp_account_type', 'customer');
//                        $customerRepository->setCustomAttribute('ecc_contact_code', $customer->getEccContactCode());
                        $extensionAttributes = $customerRepository->getExtensionAttributes();
                        /** get current extension attributes from entity **/
                        $extensionAttributes->setEccMultiErpId($erpAccount->getId());
                        $extensionAttributes->setEccMultiContactCode($customer->getEccContactCode());
                        $extensionAttributes->setEccMultiErpType('customer');
                        $customerRepository->setExtensionAttributes($extensionAttributes);

                    } else {
                        $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
                    }

                    // end work around
                    // Now customer custom attribute data have been reste due to issue so save it again using Repository
                    $this->customerRepository->save($customerRepository);
                    $session->setCustomerAsLoggedIn($customer);
                    $customer->sendNewAccountEmail(
                        'registered', $session->getBeforeAuthUrl(), $this->storeManager->getStore()->getId()
                    );
                    // display success message on first entry after account created
                    $this->_addSuccesMessage();
                }

                //send CUAU to create the login in ERP.
                if ($this->scopeConfig->getValue('epicor_comm_enabled_messages/cnc_request/send_automate_cnc_request',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {

                    if ($this->getRequest()->getPost('firstname') && $this->getRequest()->getPost('lastname')) {
                        $name = $this->getRequest()->getPost('firstname') . ' ' . $this->getRequest()->getPost('lastname');
                    } else {
                        $name = $this->getRequest()->getPost('firstname');
                    }

                    $registeredData = $this->getRequest()->getPost('registered');
                    $formData = array(
                        'login_id' => 'true',
                        'name' => $name,
                        'telephone_number' => isset($registeredData['phone']) ? $registeredData['phone'] : '' ,
                        'fax_number' => isset($registeredData['fax_number']) ? $registeredData['fax_number'] : '',
                        'email_address' => $this->getRequest()->getPost('email'),
                        'function' => null,
                    );
                    $oldFormData = false;
                    $message = $this->customerconnectMessageRequestCuau;
                    $message->addContact('A', $formData, $oldFormData);
                    $this->sendUpdate($message, $customer->getEccErpaccountId(), $customerRepository);
                }

                $this->eventManager->dispatch('ecc_business_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );
                return true;
            } else {
                $session->setCustomerFormData($this->getRequest()->getPostValue());
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
                $url = $this->_url->getUrl('customer/account/forgotpassword');
                $message = __('There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                    $url);
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $this->messageManager->addErrorMessage($message);
        } catch (\Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost()->toArray())
                ->addException($e, __('Cannot save the customer.'));
        }
        return false;
    }

    /**
     * @param $message
     * @param $erpAccountId
     * @param $customerRepository
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
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
                ->setLanguageCode($helper->getLanguageMapping($this->_localeResolver->getLocale()));
            if ($message->sendMessage()) {
                $response = $message->getResponse();
                $contacts = $xmlHelper->varienToArray($response->getCustomer()->getContacts());
                $currentEmail = $customerRepository->getEmail();

                if (isset($contacts['contact']['email_address'])) {
                    if ($currentEmail === $contacts['contact']['email_address']) {
                        $filteredContact[0] = $contacts['contact'];
                    }
                } else {
                    $filteredContact = array_values(array_filter($contacts['contact'],
                        function ($arrayValue) use ($currentEmail) {
                            return $arrayValue['email_address'] == $currentEmail;
                        }));
                }

                if (empty($filteredContact[0]['contact_code'])) {
                    $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
                } else {
                    $customerRepository->setCustomAttribute('ecc_contact_code', $filteredContact[0]['contact_code']);
                    $customerRepository->setCustomAttribute('ecc_cuco_pending', '0');
                }
            } else {
                $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
            }
        } else {
            $customerRepository->setCustomAttribute('ecc_cuco_pending', '1');
        }
        $this->customerRepository->save($customerRepository);
    }

    /**
     * @return string
     */
    public function getB2bNewAccountSuccessUrl()
    {
        $successRedirection = self::DEFAULT_ACCOUNT_SUCCESS_URL;
        return $successRedirection;
    }


    protected function _addSuccesMessage()
    {
        if ($this->successMessageDisplayed == false) {
            $successMessage = $this->scopeConfig->getValue('epicor_b2b/registration/customer_success_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (!empty($successMessage)) {
                $this->messageManager->addSuccessMessage(__($this->scopeConfig->getValue('epicor_b2b/registration/customer_success_message',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
            }
        }
        $this->successMessageDisplayed = true;
    }

    /**
     * @return string
     */
    protected function _getRegUrl()
    {
        return 'customer/account/create/';
    }

    /**
     * @return string
     */
    protected function _getSuccessUrl()
    {
        return 'customer/account/';
    }
}
