<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Account;

use Epicor\Customerconnect\Model\Message\Email\Sender\CustomerSender;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\ScopeInterface;

class SaveContact extends \Epicor\Customerconnect\Controller\Account\SaveContact
{


    /**
     * @var bool
     */
    private $hasAutoAsign = false;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuau
     */
    protected $customerconnectMessageRequestCuau;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /*
     * @var  \Epicor\Dealerconnect\Model\NewCustomerContactFactory 
     */
    protected $model_newcontact;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeDateTimeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

    /**
     *
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    protected $_stores;
    protected $_storeIds;
    protected $_websites;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var CustomerSender|null
     */
    private $customerSender;

    /**
     * SaveContact constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Epicor\Comm\Helper\Data $commHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory
     * @param \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory
     * @param \Epicor\Customerconnect\Helper\Data $customerconnectHelper
     * @param \Magento\Framework\Session\Generic $generic
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Epicor\Common\Helper\Access $commonAccessHelper
     * @param \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Epicor\Dealerconnect\Model\NewCustomerContactFactory $model_newcontact
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Common\Helper\Xml $commonXmlHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Epicor\AccessRight\Model\RoleModel\CustomerFactory $accessroleCustomerFactory
     * @param CustomerSender|null $customerSender
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Model\Message\Request\Cuau $customerconnectMessageRequestCuau,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Epicor\Dealerconnect\Model\NewCustomerContactFactory $model_newcontact,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Xml $commonXmlHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\AccessRight\Model\RoleModel\CustomerFactory $accessroleCustomerFactory,
        CustomerSender $customerSender = null
    )
    {
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->storeManager = $storeManager;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectMessageRequestCuau = $customerconnectMessageRequestCuau;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->model_newcontact = $model_newcontact;
        $this->commHelper = $commHelper;
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
        $this->mathRandom = $mathRandom;
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;
        $this->registry = $registry;
        $this->commonXmlHelper = $commonXmlHelper;
        $this->accessroleCustomerFactory = $accessroleCustomerFactory;
        $this->eventManager = $eventManager;
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory,
            $commHelper,
            $customerResourceModelCustomerCollectionFactory,
            $commonAccessGroupCustomerFactory,
            $customerconnectHelper,
            $generic,
            $cache,
            $customerCustomerFactory,
            $storeManager,
            $commonAccessHelper,
            $customerconnectMessageRequestCuau,
            $resultJsonFactory,
            $customerRepository
        );
        $this->customerSender = $customerSender;
    }

    public function execute()
    {
        $helper = $this->customerconnectHelper;

        $data = $this->getRequest()->getPost();
        $customer = $this->customerCustomerFactory->create();
        $customer->setWebsiteId($this->storeManager->getDefaultStoreView()->getWebsiteId());
        $error = false;
        $erpAccount = $this->commHelper->getErpAccountInfo();
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
        if ($erpAccount) {
            $accountNumber = $erpAccount->getErpCode();
        }
        if ($data) {

            $form_data = json_decode($data['json_form_data'], true);
            $form_data['name'] = $form_data['firstname'] . ' ' . $form_data['lastname'];
            $LoginModeType = 2;
            if (!empty($form_data['login_mode_type'])) {
                $LoginModeType = $form_data['login_mode_type'];
            }
            $IsToggleAllowed = 2;
            if (isset($form_data['is_toggle_allowed']) && trim($form_data['is_toggle_allowed']) != "") {
                $IsToggleAllowed = ($form_data['is_toggle_allowed']) ? $form_data['is_toggle_allowed'] : "0";
            }
            $form_data['login_mode_type'] = $LoginModeType;
            $form_data['is_toggle_allowed'] = $IsToggleAllowed;
            $old_form_data = json_decode($form_data['old_data'], true);
            $accessRoles = isset($form_data['customer_access_roles']) ? $form_data['customer_access_roles'] : 0;
            unset($form_data['old_data']);
            $this->checkhasAutoAsign($old_form_data);
            $customerExists = false;
            $form_data['login_id'] = 'false';
            if (isset($form_data['web_enabled'])) {

                $form_data['login_id'] = 'true';
                if (!isset($old_form_data['email_address']) || $old_form_data['email_address'] != $form_data['email_address']) {
                    $customer->loadByEmail($form_data['email_address']);
                    if ($customer && !$customer->isObjectNew()) {
                        $customerExists = true;
                    }
                }
            }
            // add this otherwise the difference check will always be true and always send a message
            $form_data['contact_code'] = isset($old_form_data['contact_code']) ? $old_form_data['contact_code']: "";

            $access_groups = null;
            if (isset($form_data['access_groups'])) {
                $accessHelper = $this->commonAccessHelper;
                if ($accessHelper->customerHasAccess('Epicor_Customerconnect', 'Account', 'index', 'manage_permissions', 'view')) {
                    $this->updateContactAccessGroups($form_data['contact_code'], $form_data['access_groups']);
                }
                unset($form_data['access_groups']);
            }

            if ($customerExists) {
                $this->messageManager->addErrorMessage(__('Contact error: Email address already exists'));
                $error = true;
            } else if (
                isset($old_form_data['ecc_web_enabled'])
                && $old_form_data['ecc_web_enabled'] == 1
                && isset($form_data['contact_code'])
                && $form_data['contact_code'] == null
                && isset($form_data['login_id'])
                && $form_data['login_id'] == "false"
            ) {
                $formattedMasterShopper = ($form_data['ecc_master_shopper'] == 'y') ? '1' : '0';
                $customer->loadByEmail($form_data['email_address']);
                $customerRepository = $this->customerRepository->getById($customer->getId());
                $customerRepository->setFirstname($form_data['firstname']);
                $customerRepository->setLastname($form_data['lastname']);
                $customerRepository->setEmail($form_data['email_address']);
                $customerRepository->setCustomAttribute('ecc_function', $form_data['function']);
                $customerRepository->setCustomAttribute('ecc_telephone_number', $form_data['telephone_number']);
                $customerRepository->setCustomAttribute('ecc_fax_number', $form_data['fax_number']);
//                $customerRepository->setCustomAttribute('ecc_erpaccount_id', $customer->getEccErpaccountId());
//                $customerRepository->setCustomAttribute('ecc_erp_account_type', 'customer');
//                $customerRepository->setCustomAttribute('ecc_contact_code', $customer->getEccContactCode());
                $extensionAttributes = $customerRepository->getExtensionAttributes();
                /** get current extension attributes from entity **/
                $extensionAttributes->setEccMultiErpId($customer->getEccErpaccountId());
                $extensionAttributes->setEccMultiContactCode($customer->getEccContactCode());
                $extensionAttributes->setEccMultiErpType('customer');
                $customerRepository->setExtensionAttributes($extensionAttributes);
                $customerRepository->setCustomAttribute('ecc_master_shopper', $formattedMasterShopper);
                if (isset($form_data['ecc_hide_prices'])) {
                    $customerRepository->setCustomAttribute('ecc_hide_price', $form_data['ecc_hide_prices']);
                }
                // start access roles
                $accessRoles = isset($form_data['customer_access_roles']) ? $form_data['customer_access_roles'] : 0;

                if ($accessRoles && count($accessRoles) == 1) {
                    $accessRoles = $accessRoles[0];
                }

                if ($form_data['ecc_access_role'] == 0) {
                    $customerRepository->setCustomAttribute('ecc_access_rights', 0);
                } elseif ($accessRoles || (empty($accessRoles) && $this->hasAutoAsign)) {
                    $customerRepository->setCustomAttribute('ecc_access_rights', 1);
                    $customerRepository->setCustomAttribute('ecc_access_roles', $accessRoles);
                    $this->saveCustomerAccessRoles($customer->getId(), $accessRoles);
                } else {
                    $customerRepository->setCustomAttribute('ecc_access_rights', 2);
                }

                // end access roles
                $customerRepository->setCustomAttribute('ecc_is_toggle_allowed', $form_data['is_toggle_allowed']);
                $customerRepository->setCustomAttribute('ecc_login_mode_type', $form_data['login_mode_type']);
                $customerRepository->setCustomAttribute('ecc_cuco_pending', $customer->getEccCucoPending());
                $customerRepository->setCustomAttribute('ecc_erp_login_id', $customer->getEccErpLoginId());

                $this->customerRepository->save($customerRepository);
                $this->messageManager->addSuccess(__('Contact updated successfully'));
                $resultData = json_encode(array('redirect' => $this->_url->getUrl('customerconnect/account/'), 'type' => 'success'));

            } else if ($old_form_data != $form_data) {

                $message = $this->customerconnectMessageRequestCuau;

                if (empty($old_form_data)) {
                    $action = 'A';
                } else {
                    if ($old_form_data['source'] === $helper::SYNC_OPTION_ONLY_ECC) {
                        $action = 'A';
                    } else {
                        $action = 'U';
                    }
                }
                $message->addContact($action, $form_data, $old_form_data);
                if ($action == 'U') {
                    $this->_successMsg = __('Contact updated successfully');
                    $this->_errorMsg = __('Failed to update Contact');
                } else {
                    $this->_successMsg = __('Contact added successfully');
                    $this->_errorMsg = __('Failed to add Contact');
                }
                $cusCreated = $this->createEccCustomer($form_data, $accountNumber, $old_form_data);
                $resultData = $this->sendUpdate($message);
                if ($cusCreated) {
                    foreach ($this->_websites as $website) {
                        $customer = $this->customerCustomerFactory->create();
                        $customer->setWebsiteId($website);
                        $customer->loadByEmail($form_data['email_address']);
                        $this->processContactResp($resultData, $form_data['email_address'], $customer);
                        unset($customer);
                    }
                }
            } else {
                $this->messageManager->addNoticeMessage(__('No changes made to Contact'));
                $error = true;
            }
        } else {
            $error = true;
        }

        if ($error) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //echo json_encode(array('redirect' => Mage::getUrl('customerconnect/account/'), 'type' => 'success'));
            $resultData = json_encode(array('redirect' => $this->_url->getUrl('customerconnect/account/'), 'type' => 'success'));
            //M1 > M2 Translation End
        }

        if ($this->registry->registry('logout_forcefully')) {
            $this->customerSession->destroy();
        }
        $result = $this->resultJsonFactory->create();
        $result->setData($resultData);

        return $result;
    }

    protected function processContactResp($resultData, $currentEmail, $customer)
    {

        if (!$resultData['error']) {
            $response = $resultData['message']->getResponse();
            $xmlHelper = $this->commonXmlHelper;
            /* @var $helper Epicor_Common_Helper_Xml */
            $contacts = $xmlHelper->varienToArray($response->getCustomer()->getContacts());
            if (isset($contacts['contact'][0])) {
                $contactsArray = $contacts['contact'];
            } else {
                $contactsArray [] = $contacts['contact'];
            }
            $filteredContact = array_values(array_filter($contactsArray, function ($arrayValue) use ($currentEmail) {
                return $arrayValue['email_address'] == $currentEmail;
            }));
            if (empty($filteredContact[0]['contact_code'])) {
                $customer->setEccCucoPending('1');
            } else {
                $customer->setEccContactCode($filteredContact[0]['contact_code']);
                $customer->setEccCucoPending('0');
            }
            if (isset($filteredContact[0]['login_id']) && $filteredContact[0]['login_id']) {
                $customer->setEccErpLoginId($filteredContact[0]['login_id']);
            }
        } else {
            $customer->setEccCucoPending('1');
        }
        $this->commHelper->saveCustomerInfo($customer, $customer->getEccErpaccountId());


        $this->registry->unregister('erp_acct_counts_' . $customer->getId());
        $linkedErpCount = $customer->getErpAcctCounts();
        if (!empty($linkedErpCount) && count($linkedErpCount) == 1) {
            $this->setDefaultAddress($customer->getEccErpaccountId(), $customer);
        }

    }

    protected function createEccCustomer($form_data, $accountNumber, $old_form_data)
    {

        $storeId = $this->storeManager->getStore()->getId();
        $brand = $this->commHelper->getStoreBranding($storeId);
        $company = $brand->getCompany();
        $erpCustomer = $this->commHelper->getErpAccountByAccountNumber($accountNumber, 'Customer');
        $currencies = array_keys($erpCustomer->getAllCurrencyData());
        $websites = $this->_getWebsitesForCurrenciesAndBranding($currencies);
        $this->_websites = $websites;
        $returnType = true;
        foreach ($websites as $website) {
            $store = $this->storeManager->getWebsite($website)->getDefaultStore();
            if (!isset($form_data['web_enabled']) && isset($old_form_data['login_id'])) {
                if ($this->isUpdateable('contacts_contact_delete_update', true, $store->getId())) {
                    try {
                        $this->_deleteContact($form_data['email_address'], $erpCustomer, $website);
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__('Contact delete failed.'));
                    }
                    $returnType = false;
                }
            } else {
                $returnType = $this->_processContact($form_data, $erpCustomer, $website, $store, $returnType);
            }
        }
        return $returnType;
    }

    protected function _getWebsitesForCurrenciesAndBranding($currencies)
    {

        if (!is_array($currencies)) {
            $currencies = array($currencies);
        }
        if ($this->scopeConfig->isSetFlag('customer/account_share/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $stores = $this->_loadStores();
            $websites = array();
            foreach ($stores as $store) {
                /* @var $store Mage_Core_Model_Store */
                if (!in_array($store->getWebsiteId(), $websites) && in_array($store->getWebsite()->getBaseCurrencyCode(), $currencies)) {
                    $websites[] = $store->getWebsiteId();
                }
            }
        } else {
            $websites = array($this->storeManager->getStore()->getWebsite()->getWebsiteId());
        }
        return $websites;
    }

    protected function _loadStores()
    {

        if (is_null($this->_stores)) {
            $this->_stores = array();
            $this->_storeIds = array();
            $brandStores = $this->commHelper->getStoreFromBranding(null);
            $this->_stores = $this->_stores + $brandStores;

            foreach ($this->_stores as $store) {
                $this->_storeIds[] = $store->getId();
            }
        }
        return $this->_stores;
    }

    protected function _processContact($form_data, $erpCustomer, $website, $store)
    {

        $name = $form_data['name'];
        $function = $form_data['function'];
        $telephoneNumber = $form_data['telephone_number'];
        $faxNumber = $form_data['fax_number'];
        $emailAddress = $form_data['email_address'];
        $loginId = $form_data['login_id'];
        $masterShopper = $form_data['ecc_master_shopper'];
        $LoginModeType = $form_data['login_mode_type'];
        $IsToggleAllowed = $form_data['is_toggle_allowed'];
        $supplierConnect = $this->moduleManager->isEnabled('Epicor_Supplierconnect');
        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        $collection->addAttributeToFilter('email', $form_data['email_address']);
        $collection->addAttributeToFilter('ecc_erpaccount_id', $erpCustomer->getId());
        $collection->addFieldToFilter('website_id', $website);
        $collection->addAttributeToSelect('*');
        $customer = $collection->getFirstItem();

        /* @var $customer \Epicor\Comm\Model\Customer */

        if (empty($customer) || $customer->isObjectNew()) {
            $customer = $this->customerCustomerFactory->create();
            $customer->setForceErpAccountGroup(1);
            $this->_contactExists = false;
        } else {
            $this->_contactExists = true;
        }
        if (!$this->_contactExists && !isset($form_data['web_enabled'])) {
            return false;
        }
        if (!$this->_contactExists || $this->_contactExists && $this->isUpdateable('contacts_update', true, $store->getId())) {
            $customer->setWebsiteId($website);
            $new = $customer->isObjectNew();
            $customer->setStore($store);

            $this->updateContactField('ecc_function', $function, 'contacts_contact_function_update', $customer);
            $this->updateContactField('ecc_telephone_number', $telephoneNumber, 'contacts_contact_telephone_number_update', $customer);
            $this->updateContactField('ecc_fax_number', $faxNumber, 'contacts_contact_fax_number_update', $customer);
            $this->updateContactField('email', $emailAddress, 'contacts_contact_email_update', $customer);
            $nameParts = explode(' ', $name, 3);

            if (!$this->_contactExists || $this->_contactExists && $this->isUpdateable('contacts_contact_name_update', true, $store->getId())) {
                $customer->setFirstname($nameParts[0]);

                if (count($nameParts) == 3) {
                    $customer->setMiddlename($nameParts[1]);
                    $customer->setLastname($nameParts[2]);
                } else {
                    $customer->setLastname($nameParts[1]);
                }
            }
            if ($supplierConnect) {

                if ($erpCustomer->isTypeCustomer()) {
                    $customer->setData('ecc_erpaccount_id', $erpCustomer->getId());
                } else if ($erpCustomer->isTypeSupplier()) {
                    throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ACCOUNT_CODE, $erpCustomer->getShortCode()), self::STATUS_INVALID_ACCOUNT_CODE);
                }
            } else {
                $customer->setData('ecc_erpaccount_id', $erpCustomer->getId());
            }

            if ($new) {
                $customer->setPassword($this->mathRandom->getRandomString(10));
            }

            $formattedMasterShopper = ($masterShopper == 'y') ? '1' : '0';
            if ($this->_contactExists) {
                if (($masterShopper || $masterShopper === '0')) {
                    $customer->setData('ecc_master_shopper', $formattedMasterShopper);
                }
            } else {
                //save Created at date in adjusted for locale
                $customer->setCreatedAt($this->dateTimeDateTimeFactory->create()->date('Y-m-d H:i:s'));
                if ($masterShopper || $masterShopper === '0') {
                    $customer->setData('ecc_master_shopper', $formattedMasterShopper);
                } else {
                    $defaultMasterShopper = $this->getConfigFlag('master_shopper_default_value');
                    $customer->setData('ecc_master_shopper', $defaultMasterShopper);
                }
            }
            /* Save is toggle allowed & login Type for dealers */
            $customer->setData('ecc_is_toggle_allowed', $IsToggleAllowed);
            $customer->setData('ecc_login_mode_type', $LoginModeType);
            if (isset($form_data['ecc_hide_prices'])) {
                $customer->setData('ecc_hide_price', $form_data['ecc_hide_prices']);
            }

            // start access roles
            $accessRoles = isset($form_data['customer_access_roles']) ? $form_data['customer_access_roles'] : 0;

            if ($accessRoles && count($accessRoles) == 1) {
                $accessRoles = $accessRoles[0];
            }

            if ($form_data['ecc_access_role'] == 0) {
                $customer->setData('ecc_access_rights', 0);
            } elseif ($accessRoles || (is_array($accessRoles) && empty($accessRoles) && $this->hasAutoAsign)) {
                $customer->setData('ecc_access_rights', 1);
                $customer->setData('ecc_access_roles', $accessRoles);
            } else {
                $customer->setData('ecc_access_rights', 2);
            }

            // end access roles
            $this->commHelper->saveCustomerInfo($customer, $erpCustomer->getId());
            // start access roles
            $accessRoles = isset($form_data['customer_access_roles']) ? $form_data['customer_access_roles'] : 0;

            if ($accessRoles && count($accessRoles) == 1) {
                $accessRoles = $accessRoles[0];
            }

            if ($form_data['ecc_access_role'] == 0) {
                $customer->setData('ecc_access_rights', 0);
            } elseif ($accessRoles || (is_array($accessRoles) && empty($accessRoles) && $this->hasAutoAsign)) {
                $customer->setData('ecc_access_rights', 1);
                $customer->setData('ecc_access_roles', $accessRoles);
                $this->saveCustomerAccessRoles($customer->getId(), $accessRoles);

            } else {
                $customer->setData('ecc_access_rights', 2);
            }

            // end access roles
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $storeId = $this->customerSender->getStoreFromWebsite($website);
            if ($new && $this->getConfigFlag('send_emails', $storeScope, $storeId)) {
                $templateEmailConfigPath = Customer::XML_PATH_REGISTER_EMAIL_TEMPLATE;
                $this->customerSender
                    ->send($customer, $templateEmailConfigPath, $storeId);
            }
        }
        unset($collection);
        unset($customer);
        return true;
    }

    protected function _deleteContact($email, $erpCustomer, $website)
    {
        if (!$erpCustomer) {
            // load by previous erp code as erp accoutn no longer exists
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            //  $collection->addAttributeToFilter('ecc_contact_code', $contactCode);
            $collection->addAttributeToFilter('email', $email);
            $collection->addAttributeToFilter('ecc_previous_erpaccount', $accountNumber);
            $collection->addFieldToFilter('website_id', $website);
            $customer = $collection->getFirstItem();
        } else {
            // load by erp account id
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            //  $collection->addAttributeToFilter('ecc_contact_code', $contactCode);
            $collection->addAttributeToFilter('email', $email);
            $collection->addAttributeToFilter('ecc_erpaccount_id', $erpCustomer->getId());
            $collection->addFieldToFilter('website_id', $website);
            $customer = $collection->getFirstItem();
        }

        /* @var $customer \Magento\Customer\Model\Customer */

        if (!empty($customer) || !$customer->isObjectNew()) {
            $linkedErpCount = $customer->getErpAcctCounts();
            if (!empty($linkedErpCount) && count($linkedErpCount) > 1) {
                $customer->deleteErpAcctById($erpCustomer->getId());
                $this->eventManager->dispatch(
                    'ecc_cuco_del_addresses', ['customer' => $customer, 'erp_account_Id' => $erpCustomer->getId()]
                );
                $this->registry->unregister('erp_acct_counts_' . $customer->getId());
                $linkedErpCount = $customer->getErpAcctCounts();
                if (!empty($linkedErpCount) && count($linkedErpCount) == 1) {

                    $this->setDefaultAddress($linkedErpCount[0]['erp_account_id'], $customer);
                }
                if ($email == $this->customerSession->getCustomer()->getEmail()) {

                    $this->registry->unregister('logout_forcefully');
                    $this->registry->register('logout_forcefully', true);
                }

                //$this->customerSession->setMasqueradeAccountId(null);
            } else {
                $customer->delete();
            }
        }
    }

    protected function updateContactField($field, $value, $config, $customer)
    {
        if (!$this->_contactExists || $this->_contactExists && $this->isUpdateable($config, true, $customer->getStore()->getId())) {
            $customer->setData($field, $value);
        }
    }

    protected function setDefaultAddress($erp_account_id, $customer)
    {

        $erpAccount = $this->commHelper->getErpAccountInfo($erp_account_id);
        $deladdressCode = $erpAccount->getDefaultDeliveryAddressCode();
        $delinvCode = $erpAccount->getDefaultInvoiceAddressCode();

        $addresscollection = $customer->getAddressesCollection();
        $erpCustomerGroupCode = $erpAccount->getErpCode();
        $gcattributes = [
            ['attribute' => 'ecc_erp_group_code', 'eq' => $erpCustomerGroupCode],
            ['attribute' => 'ecc_erp_group_code', 'null' => true],
        ];
        $addresscollection->addAttributeToFilter($gcattributes, null, 'left');
        $addresscollection->addAttributeToFilter('ecc_erp_address_code', array('in' => [$deladdressCode, $delinvCode]));
        $addresscollection->load();

        $items = $addresscollection->getItems();
        $customerRepository = $this->customerRepository->getById($customer->getId());
        foreach ($items as $item) {
            if ($deladdressCode == $item->getData('ecc_erp_address_code')) {
                $customerRepository->setDefaultShipping($item->getId());
            }
            if ($delinvCode == $item->getData('ecc_erp_address_code')) {
                $customerRepository->setDefaultBilling($item->getId());
            }
        }
        $this->customerRepository->save($customerRepository);
    }

    protected function isUpdateable($config, $exists = true, $storeId)
    {
        $path = $this->getConfigBase() . $config;
        $flag = $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return (!$exists || $exists == $flag);
    }

    protected function getConfigFlag($config, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store = null)
    {

        $path = $this->getConfigBase() . $config;
        return $this->scopeConfig->isSetFlag($path, $scope, $store);
    }

    protected function getConfigBase()
    {
        return 'customerconnect_enabled_messages/CUCO_mapping/';
    }


    /**
     * Check is their any auto assign roles
     * @param array $oldformdata
     *
     * @return boolean
     */
    protected function checkhasAutoAsign($oldformdata)
    {
        if (isset($oldformdata['ecc_access_roles']) && !empty($oldformdata['ecc_access_roles'])) {
            foreach ($oldformdata['ecc_access_roles'] as $accessRole) {
                if ($accessRole['autoAssign'] == 1 && $oldformdata['ecc_access_rights'] == 1) {
                    $this->hasAutoAsign = true;
                }
            }
        }
    }

    public function saveCustomerAccessRoles($customerId, $accessRoles)
    {
        $customerAccessRoleModel = $this->accessroleCustomerFactory->create();
        $customerAccessRoleModelCollection = $customerAccessRoleModel->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('by_customer', 1);
        if ($customerAccessRoleModelCollection->getData()) {
            foreach ($customerAccessRoleModelCollection as $val) {
                if ($val['by_role'] == 0 && $val['by_customer'] == 1) {
                    $val->delete();
                }
                if ($val['by_role'] == 1 && $val['by_customer'] == 1) {
                    $customerAccessRoleModel->load($val['id']);
                    $model = $customerAccessRoleModel->load($val['id'])->setByCustomer(0);
                    $model->save();
                }
            }
        }

        if (!is_array($accessRoles)) {
            $accessRoles = [$accessRoles];
        }
        foreach ($accessRoles as $accessRole) {
            if ($accessRole) {
                $customerAccessRoleModel = $this->accessroleCustomerFactory->create();
                $customerAccessRoleModel->setAccessRoleId($accessRole);
                $customerAccessRoleModel->setCustomerId($customerId);
                $customerAccessRoleModel->setByCustomer(1);
                $customerAccessRoleModel->setByRole(0);
                $customerAccessRoleModel->save();
            }
        }
    }

}
