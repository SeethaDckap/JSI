<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Upload;

use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\ScopeInterface;

/**
 * Response CUCO - Upload Customer Connect Users
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cuco extends \Epicor\Customerconnect\Model\Message\Upload
{

    protected $_contactExists;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeDateTimeFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;
      /*
     * @var  \Epicor\Dealerconnect\Model\ResourceModel\NewCustomerContact\CollectionFactory
     */
    protected $collection_newcontact;

    protected $commHelper;

    /**
     * ShareConfig.
     *
     * @var ShareConfig
     */
    protected $shareConfig;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Email\Sender\CustomerSender|null
     */
    private $customerSender;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount
     */
    private $erpCustomer;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Epicor\Comm\Model\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Math\Random $mathRandom,
        \Epicor\Dealerconnect\Model\ResourceModel\NewCustomerContact\CollectionFactory $collection_newcontact,
        ShareConfig $shareConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Epicor\Customerconnect\Model\Message\Email\Sender\CustomerSender $customerSender = null,
        array $data = []
    )
    {
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;
        $this->moduleManager = $moduleManager;
        $this->mathRandom = $mathRandom;
        $this->collection_newcontact = $collection_newcontact;
        $this->commHelper = $context->getCommHelper();
        $this->shareConfig = $shareConfig;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('customerconnect_enabled_messages/CUCO_mapping/');
        $this->setMessageType('CUCO');
        $this->setLicenseType(array('Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_customer', true, true);
        $this->customerSender = $customerSender;
    }

    public function processAction()
    {
        $brands = $this->getRequest()->getBrands();
        $brand = null;
        if (!is_null($brands))
            $brand = $brands->getBrand();

        if (is_array($brand))
            $brand = $brand[0];

        if (empty($brand) || !$brand->getCompany())
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
            $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            //M1 > M2 Translation End

        $this->_company = $brand->getCompany();

        $accountCode = $this->getVarienData('account_number', $this->getRequest());

        if (empty($accountCode)) {
            throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ACCOUNT_CODE, $accountCode), self::STATUS_INVALID_ACCOUNT_CODE);
        }

        if (!empty($this->_company)) {
            $delimiter = $this->getHelper()->getUOMSeparator();
            $request = $this->getRequest();
            $this->setVarienData('account_number', $request, $this->_company . $delimiter . $accountCode);
//            $this->setVarienData('account_number', $this->getRequest(), $this->_company . $delimiter . $accountCode);
        }

        $accountNumber = $this->getVarienData('account_number', $this->getRequest());
        $this->setMessageSubject($accountNumber);

        $this->erpCustomer = $this->getErpAccount($accountNumber);

        $currencies = array_keys($this->erpCustomer->getAllCurrencyData());
        $websites = $this->_getWebsitesForCurrenciesAndBranding($currencies);
        $contacts = array();
        $contactsGroup = $this->getRequest()->getContacts();

        if ($contactsGroup instanceof \Epicor\Common\Model\Xmlvarien) {
            $contacts = $contactsGroup->getasarrayContact();
        }

        $contacts = $this->getVarienDataArray('contacts', $this->getRequest());

        if (empty($this->erpCustomer) || $this->erpCustomer->isObjectNew()) {

            // ERP account doesnt exist, check if we're processing deletes only
            // If one or more is not a delete, throw an error
            // otherwise process as normal

            $deleting = true;

            if (!empty($contacts)) {
                $this->setMessageSecondarySubject($this->createMessageSecondarySubject($contacts));
                foreach ($contacts as $contact) {
                    $delete = $this->getVarienDataFlag('contact_delete', $contact);
                    if (!$delete) {
                        $deleting = false;
                    }
                }
            } else {
                $deleting = false;
            }

            if (!$deleting) {
                throw new \Exception($this->getErrorDescription(self::STATUS_CUSTOMER_NOT_ON_FILE, $this->getHelper()->removeDelimiter($accountNumber)), self::STATUS_CUSTOMER_NOT_ON_FILE);
            } else {
                foreach ($contacts as $contact) {
                    $delete = $this->getVarienDataFlag('contact_delete', $contact);
                    if ($delete) {
                        if ($this->isUpdateable('contacts_contact_delete_update')) {
                            foreach ($websites as $website) {
                                $this->_deleteContact($contact, false, $website, $accountNumber);
                            }
                        }
                    }
                }
            }
        } else {

            if (empty($contacts)) {
                throw new \Exception('No contacts provided', self::STATUS_GENERAL_ERROR);
            }

            $this->setMessageSecondarySubject($this->createMessageSecondarySubject($contacts));
            foreach ($contacts as $contact) {
                $delete = $this->getVarienDataFlag('contact_delete', $contact);
                foreach ($websites as $website) {
                    if ($delete) {
                        if ($this->isUpdateable('contacts_contact_delete_update')) {
                            $this->_deleteContact($contact, $this->erpCustomer, $website);
                        }
                    } else {
                        $this->_processContact($contact, $this->erpCustomer, $website);
                    }
                }
            }
        }
    }

    private function createMessageSecondarySubject($contacts)
    {
        $subj = '';
        switch (count($contacts)) {
            case null:
            case 0:
                break;

            case 1:
                $subj = 'Contact Uploaded: ' . $contacts[0]->getEmailAddress();
                break;

            default:
                $callback = function($contact) {
                    return $contact->getEmailAddress();
                };
                $emailAddresses = implode(', ', array_map($callback, $contacts));
                $subj = 'Contacts Uploaded: <a title="Contacts uploaded from ERP: ' . $emailAddresses . '">' . count($contacts) . "</a>";
                break;
        }

        return $subj;
    }

    /**
     * Processes a contact with the given data
     *
     * @param \Epicor\Comm\Model\Xmlvarien $contact
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomer
     * @param $website
     * @throws \Exception
     */
    private function _processContact($contact, $erpCustomer, $website)
    {
        $contactCode = $this->getVarienData('contact_code', $contact);
        $contactId = $this->getVarienData('contact_id', $contact);
        $name = $this->getVarienData('contact_name', $contact);
        $function = $this->getVarienData('contact_function', $contact);
        $telephoneNumber = $this->getVarienData('contact_telephone', $contact);
        $faxNumber = $this->getVarienData('contact_fax', $contact);
        $mobileNumber = $contact->getMobileNumber();
        $emailAddress = $this->getVarienData('contact_email', $contact);
        $loginId = $this->getVarienData('contact_login', $contact);
        $masterShopper = $this->getVarienData('is_master_shopper', $contact);
        $branchPickupAllowed = $this->getRequest('is_branch_pickup_allowed');
        $delAddresses = false;

        if (empty($emailAddress)) {
            throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_CONTACT, 'Email address is empty'), self::STATUS_INVALID_CONTACT);
        }

        // only save the customer if the login ID is set
        if (!empty($loginId)) {

            $commHelpers = $this->commHelper;
            $isValidLicense =  $commHelpers->isLicensedFor(array('Dealer_Portal'));
            if(in_array($erpCustomer->getAccountType(),array('Dealer','Distributor') ) && (!$isValidLicense)) {
                throw new \Exception(
                'License key not valid for this feature', \Epicor\Comm\Model\Message::STATUS_ERP_LICENSE_REQUIRED
                );
            }

            $supplierConnect = $this->moduleManager->isEnabled('Epicor_Supplierconnect');

            if(!empty($contactCode)){
                $field = 'email';
                $val = $emailAddress;
            }else{
                $field = 'ecc_contact_code';
                $val = $contactCode;
            }
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter($field, $val);
            // Check whether current customers sharing scope is website or global.
            if ($this->shareConfig->isWebsiteScope()) {
                $collection->addFieldToFilter('website_id', $website);
            }
            /** @var  $customer \Epicor\Dealerconnect\Model\Customer */
            $customer = $collection->getFirstItem();

            $eccErpAccountIdVal = '';
            $isGuest = empty($customer->getErpAcctCounts()) ?  true : false;
            if($customer->getId()){
                $eccErpAccountIdVal = $customer->getEccErpAccountId();
                $contactCodeVal = $customer->getEccContactCode(true);
                $contactIdVal = $customer->getEccPerContactId();
                $cucoPending = $customer->getEccCucoPending();
            }
            if (!$customer->getId()
                || $customer->isObjectNew()
                || ((!empty($contactCodeVal) && $contactCodeVal !== $contactCode)
                    && (!empty($eccErpAccountIdVal) && $eccErpAccountIdVal == $erpCustomer->getId()))
                ||((!empty($contactIdVal) && $contactIdVal !== $contactId)
                    && (!empty($eccErpAccountIdVal) && $eccErpAccountIdVal != $erpCustomer->getId()))
                ||(empty($contactId) && (!empty($eccErpAccountIdVal) && $eccErpAccountIdVal != $erpCustomer->getId()))) {
                /** @var  $customer \Epicor\Dealerconnect\Model\Customer */
                $customer = $this->customerCustomerFactory->create();
                $customer->setForceErpAccountGroup(1);
                $this->_contactExists = false;
            } else {

                if($customer->getEccCucoPending()){
                    $this->_contactExists = true;
                }else{
                    $this->_contactExists = true;
                    if($isGuest){
                        $customer = $this->customerCustomerFactory->create();
                        $customer->setForceErpAccountGroup(1);
                        $this->_contactExists = false;
                    }
                }
            }
            if (!$this->_contactExists || $this->_contactExists && $this->isUpdateable('contacts_update')) {
                $new = $customer->isObjectNew();
                if($new){
                    $newLinkToken = $this->mathRandom->getUniqueHash();
                    $customer->setRpToken($newLinkToken);
                    $customer->setRpTokenCreatedAt(
                        $this->dateTimeDateTimeFactory->create()->date('Y-m-d H:i:s')
                    );
                }

                // Check existing customers scope is website or global.
                if ($this->shareConfig->isWebsiteScope() || $new) {
                    $store = $this->storeManager->getWebsite($website)->getDefaultStore();
                    $customer->setWebsiteId($website);
                    $customer->setStore($store);
                }

                $this->updateContactField('ecc_function', $function, 'contacts_contact_function_update', $customer);
                $this->updateContactField('ecc_telephone_number', $telephoneNumber, 'contacts_contact_telephone_number_update', $customer);
                $this->updateContactField('ecc_fax_number', $faxNumber, 'contacts_contact_fax_number_update', $customer);
                $this->updateContactField('ecc_mobile_number', $mobileNumber, 'contacts_contact_mobile_number_update', $customer);
                $this->updateContactField('email', $emailAddress, 'contacts_contact_email_update', $customer);

                $customer->setData('ecc_contact_code', $contactCode);
                $customer->setData('ecc_per_contact_id', $contactId);
                if ($contactCode) {
                    $customer->setEccCucoPending(0);
                } else {
                    $customer->setEccCucoPending(1);
                }
                $customer->setData('ecc_erp_login_id', $loginId);
                if (strpos($name, " ") === false) {
                    throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_CONTACT, 'Last name has not been provided in name field'), self::STATUS_INVALID_CONTACT);
                }
                $nameParts = explode(' ', $name, 3);

                if (!$this->_contactExists || $this->_contactExists && $this->isUpdateable('contacts_contact_name_update')) {
                    $this->setName($nameParts[0], $customer, 'firstname');

                    if (count($nameParts) == 3) {
                        $customer->setMiddlename($nameParts[1]);
                        $this->setName($nameParts[2], $customer, 'lastname');
                    } else {
                        $this->setName($nameParts[1], $customer, 'lastname');
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

                $this->processLocations($contact, $customer);

                $this->setMasterShopperValue($customer);

                if (!$this->_contactExists) {
                    //save Created at date in adjusted for locale
                    $customer->setCreatedAt($this->dateTimeDateTimeFactory->create()->date('Y-m-d H:i:s'));
                }

                //Handling branch pickup message request
                $accountVals = $branchPickupAllowed;
                $getBranchPickupAllowed = ($accountVals == '0') ? 'false' : $accountVals;
                if (($getBranchPickupAllowed) && ($this->isUpdateable('cuco_branch_pickup'))) {
                    if (in_array($getBranchPickupAllowed, array('true', '1', 'Y'))) {
                        $customer->setData('ecc_is_branch_pickup_allowed', 1);
                    } elseif (in_array($getBranchPickupAllowed, array('false', '0', 'N'))) {
                        $customer->setData('ecc_is_branch_pickup_allowed', 0);
                    } elseif ($getBranchPickupAllowed == "global") {
                        $customer->setData('ecc_is_branch_pickup_allowed', 2);
                    }
                } else {
                    if ($customer->getId()) {
                        $getCustomerBranchPickup = $customer->getEccIsBranchPickupAllowed();
                        $customer->setData('ecc_is_branch_pickup_allowed', $getCustomerBranchPickup);
                    } else {
                        if ($getBranchPickupAllowed) {
                            if (in_array($getBranchPickupAllowed, array('true', '1', 'Y'))) {
                                $customer->setData('ecc_is_branch_pickup_allowed', 1);
                            } elseif (in_array($getBranchPickupAllowed, array('false', '0', 'N'))) {
                                $customer->setData('ecc_is_branch_pickup_allowed', 0);
                            } elseif ($getBranchPickupAllowed == "global") {
                                $customer->setData('ecc_is_branch_pickup_allowed', 2);
                            }
                        }
                    }
                }

                //hide price options
                if ($customer->getId()) {
                    $customer->setData('ecc_hide_price', $customer->getEccHidePrice());
                }

                $prevSaved_contactData =$this->getSavedContactDetails($emailAddress);
                if(is_array($prevSaved_contactData) && count($prevSaved_contactData)>0){
                    if($prevSaved_contactData['is_toggle_allowed']!=null)
                          $customer->setData('ecc_is_toggle_allowed', (int)$prevSaved_contactData['is_toggle_allowed']);
                    if($prevSaved_contactData['login_mode_type']!=null)
                        $customer->setData('ecc_login_mode_type', $prevSaved_contactData['login_mode_type']);

                    $this->deleteSavedContactDetails($emailAddress);
                }

                if ($new) {
                    $delAddresses = false;
                }elseif ($cucoPending && $isGuest && $erpCustomer->getId()){
                    $delAddresses = true;
                }
                $this->commHelper->saveCustomerInfo($customer, $erpCustomer->getId(), $delAddresses);
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $storeId = $this->customerSender->getStoreFromWebsite($website);
                if ($new && $this->getConfigFlag('send_emails', $storeScope, $storeId)) {
                    //Send confirmation Email for new registered shopper.
                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            '',
                            $website
                        );
                    } else {
                        //send new account email
                        $templateEmailConfigPath = Customer::XML_PATH_REGISTER_EMAIL_TEMPLATE;
                        $this->customerSender
                            ->send($customer, $templateEmailConfigPath, $storeId);
                    }
                } elseif ($this->commHelper->canSendConversionEmail() && $delAddresses) {
                    //send guest to B2B conversion email
                    $this->commHelper->sendConversionEmail($customer);
                }
            }

            unset($customer);
            unset($collection);
        }
    }

    private function setMasterShopperValue($customer)
    {
        $defaultMasterShopper = $this->getConfigFlag('master_shopper_default_value');

        if (!$customer->getId()) {
            $customer->setData('ecc_master_shopper', $defaultMasterShopper);
        } else {
            $this->setExistingContactMasterShopperValue($customer, $defaultMasterShopper);
        }
    }

    private function setExistingContactMasterShopperValue($customer, $defaultMasterShopper)
    {
        if ($this->isUpdateable('master_shopper_update')) {
            $customer->setData('ecc_master_shopper', $defaultMasterShopper);
        }
    }


    /**
     * Deletes a contact with the given data
     *
     * @param \Epicor\Comm\Model\Xmlvarien $contact
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomer
     */
    private function _deleteContact($contact, $erpCustomer, $website, $accountNumber = '')
    {
        $contactCode = $this->getVarienData('contact_code', $contact);
        if (!$erpCustomer) {
            // load by previous erp code as erp account no longer exists
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->addAttributeToFilter('ecc_contact_code', $contactCode);
            $collection->addAttributeToFilter('ecc_previous_erpaccount', $accountNumber);
            // Check whether current customers sharing scope is website or global.
            if ($this->shareConfig->isWebsiteScope()) {
                $collection->addFieldToFilter('website_id', $website);
            }

            $customer = $collection->getFirstItem();
        } else {
            // load by erp account id
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->addAttributeToFilter('ecc_contact_code', $contactCode);
            $collection->addAttributeToFilter('ecc_erpaccount_id', $erpCustomer->getId());
            // Check whether current customers sharing scope is website or global.
            if ($this->shareConfig->isWebsiteScope()) {
                $collection->addFieldToFilter('website_id', $website);
            }
            $customer = $collection->getFirstItem();
        }

        /* @var $customer Customer */
        if (!empty($customer->getData()) || !$customer->isObjectNew()) {
            $linkedErpCount = $customer->getErpAcctCounts();
            if(!empty($linkedErpCount) && count($linkedErpCount) > 1){
                if ($erpCustomer === false) {
                    throw new \Exception($this->getErrorDescription(self::STATUS_ERP_ACCOUNT_DOESNT_EXIST));
                }

                $customer->deleteErpAcctById($erpCustomer->getId());
                $this->eventManager->dispatch(
                    'ecc_cuco_del_addresses', ['customer' => $customer, 'erp_account_Id' => $erpCustomer->getId()]
                );
            }else{
                $this->deleteSavedContactDetails($customer->getEmail());
                $customer->delete();
            }
        }
    }

    private function updateContactField($field, $value, $config, $customer)
    {
        if (!$this->_contactExists || $this->_contactExists && $this->isUpdateable($config)) {
            $customer->setData($field, $value);
        }
    }

    /**
     * Processes Locations tag
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer $customer
     */
    protected function processLocations($erpData, &$customer)
    {
        if (!$this->isUpdateable('contacts_location_update', $this->_contactExists)) {
            return;
        }

        $stores = $this->_loadStores($erpData);

        $helper = $this->commLocationsHelper->create();

        $defaultLocation = $erpData->getLocationCode();

        if ($defaultLocation) {
            $_defaultLocation = $helper->checkAndCreateLocation($defaultLocation, $this->_company, $stores);
            $defaultLocation =  is_null($_defaultLocation) ? $defaultLocation : $_defaultLocation->getCode();
            $customer->setEccDefaultLocationCode($defaultLocation);
        }

        $locations = $this->_getGroupedData('locations', 'location_code', $erpData);

        $linkType = null;
        $newLocations = array();
        foreach ($locations as $locationCode) {
            $atts = $locationCode->getData('_attributes');
            $code = $locationCode->getValue();
            $include = ($atts) ? $atts->getInclude() : '';

            if (is_null($linkType)) {
                $linkType = $include == 'Y' ? \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE : \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE;
            }

            $_location = $helper->checkAndCreateLocation($code, $this->_company, $stores);

            $newLocations[$code] = is_null($_location) ? $code : $_location->getCode();
        }

        $customer->updateLocationsFromXML($newLocations, $linkType);
        if ($newLocations) {
            $customer->setEccLocationLinkType('customer');
        } else {
            $customer->setEccLocationLinkType('');
        }
    }

    /*
     * Fetch the two contact details  saved in db
     * Is toggle Allowed & Login Mode Type
     */
    public function getSavedContactDetails($email){
        $collection =  $this->collection_newcontact->create();
        $collection->addFieldToFilter('contact_email', $email);
        return $collection->getFirstItem()->getData();
    }

     /*
     * Delete record form table ecc_newcustomer_contact which is saved by master shopper
      */
    public function deleteSavedContactDetails($email){
        $collection =  $this->collection_newcontact->create();
        $collection->addFieldToFilter('contact_email', $email);
        $collection->load();
        $items = $collection->getItems();
        if($collection->getSize()>0){
            foreach($items as $row){
                $row->delete();
            }
        }
    }


    /**
     * Set and validate customer name.
     *
     * @param mixed $name
     * @param Customer $customer
     * @param string $type
     *
     * @throws \Exception
     */
    private function setName($name, Customer &$customer, $type)
    {
        $addressLimitEnabled = $this->commHelper->isAddressLimitEnabled();
        if ($type == 'firstname') {
            $characterLimit = $this->scopeConfig->getValue('customer/address/limit_name_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($addressLimitEnabled && strlen($name) > $characterLimit) {
                throw new \Exception(__('Firstname exceeds character limit of '.$characterLimit.'.'), self::STATUS_REJECTED);
            } else {
                $customer->setFirstname($name);
            }
        } else if ($type == 'lastname') {
            $characterLimit = $this->scopeConfig->getValue('customer/address/limit_lastname_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($addressLimitEnabled && strlen($name) > $characterLimit) {
                throw new \Exception(__('Lastname exceeds character limit of '.$characterLimit.'.'), self::STATUS_REJECTED);
            } else {
                $customer->setLastname($name);
            }
        }

    }//end setName()

    /**
     * Loads the stores from the ERP
     * @param null $erpData
     * @param false $throwError
     * @return array|void
     */
    protected function _loadStores($erpData = null, $throwError = false)
    {
        parent::_loadStores($erpData, $throwError);
        if (is_null($this->erpCustomer) === false) {
            $erpStores = $this->erpCustomer->getValidStores();
            $newStores = [];
            foreach ($this->_stores as $store) {
                if (in_array($store->getId(), $erpStores)) {
                    $newStores[] = $store;
                }
            }
            $this->_stores = $newStores;
        }
        return $this->_stores;
    }


}
