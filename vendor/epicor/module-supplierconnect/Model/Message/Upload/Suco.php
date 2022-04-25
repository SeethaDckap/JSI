<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Upload;


/**
 * Response SUCO - Upload Supplier Connect Users
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Suco extends \Epicor\Supplierconnect\Model\Message\Upload
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
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /*
     * $var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected  $customerRepository;

    protected $dateTimeDateTimeFactory;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->mathRandom = $mathRandom;
        $this->customerRepository = $customerRepository;
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;
        parent::__construct(
            $context,
            $resource,
            $resourceCollection,
            $data
        );
        $this->setConfigBase('supplierconnect_enabled_messages/SUCO_mapping/');
        $this->setMessageType('SUCO');
        $this->setLicenseType(array('Supplier'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_supplier', true, true);
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

        $accountCode = $this->getVarienData('account_number', $this->getRequest());

        if (empty($accountCode)) {
            throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ACCOUNT_CODE, $accountCode), self::STATUS_INVALID_ACCOUNT_CODE);
        }

        $company = $brand->getCompany();
        if (!empty($company)) {
            $delimiter = $this->getHelper()->getUOMSeparator();
            $request = $this->getRequest();
            $this->setVarienData('account_number', $request, $company . $delimiter . $accountCode);
        }

        $accountNumber = $this->getVarienData('account_number', $this->getRequest());
        $this->setMessageSubject($accountNumber);

        $websites = $this->_getWebsites();

        $erpCustomer = $this->getErpAccount($accountNumber, 'Supplier');
        /* @var $erpCustomer Epicor_Comm_Model_Customer_Erpaccount */

        $contacts = $this->getVarienDataArray('contacts', $this->getRequest());

        if (empty($erpCustomer) || $erpCustomer->isObjectNew()) {

            // ERP account doesnt exist, check if we're processing deletes only
            // If one or more is not a delete, throw an error
            // otherwise process as normal

            $deleting = true;

            if (!empty($contacts)) {
                foreach ($contacts as $contact) {
                    $delete = $this->getVarienDataFlag('contact_delete', $contact);
                    if (!$delete) {
                        $deleting = false;
                    }
                }
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

            foreach ($contacts as $contact) {
                $delete = $this->getVarienDataFlag('contact_delete', $contact);
                foreach ($websites as $website) {
                    if ($delete) {
                        if ($this->isUpdateable('contacts_contact_delete_update')) {
                            $this->_deleteContact($contact, $erpCustomer, $website);
                        }
                    } else {
                        $this->_processContact($contact, $erpCustomer, $website);
                    }
                }
            }
        }
    }

    /**
     * Processes a contact with the given data
     *
     * @param \Epicor\Comm\Model\Xmlvarien $contact
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomer
     */
    private function _processContact($contact, $erpCustomer, $website)
    {
        $contactCode = $this->getVarienData('contact_code', $contact);
        $name = $this->getVarienData('contact_name', $contact);
        $function = $this->getVarienData('contact_function', $contact);
        $telephoneNumber = $this->getVarienData('contact_telephone', $contact);
        $faxNumber = $this->getVarienData('contact_fax', $contact);
        $mobileNumber = $contact->getMobileNumber();
        $emailAddress = $this->getVarienData('contact_email', $contact);
        $loginId = $this->getVarienData('contact_login', $contact);

        if (empty($emailAddress)) {
            throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_CONTACT, 'Email address is empty'), self::STATUS_INVALID_CONTACT);
        }

        // only save the customer if the login ID is set
        if (!empty($loginId)) {
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->setFlag('is_supplier', true);
            $collection->addAttributeToFilter('ecc_contact_code', $contactCode);
            $collection->setFlag('is_supplier', false);
            $collection->addAttributeToFilter('ecc_supplier_erpaccount_id', $erpCustomer->getId());
            $collection->addFieldToFilter('website_id', $website);

            $customer = $collection->getFirstItem();
            /* @var $customer Mage_Customer_Model_Customer */

            if (empty($customer) || $customer->isObjectNew()) {
                $customer = $this->customerCustomerFactory->create();
                $this->_contactExists = false;
            } else {
                $this->_contactExists = true;
            }
            if ((!$this->_contactExists) || ($this->_contactExists && $this->isUpdateable('contacts_update'))) {

                $customer->setWebsiteId($website);
                /* @var $customer Mage_Customer_Model_Customer */
                $new = $customer->isObjectNew();
                $store = $this->storeManager->getWebsite($website)->getDefaultStore();
                $customer->setStore($store);
                $this->updateContactField('email', $emailAddress, 'contacts_contact_email_update', $customer);
                $nameParts = explode(' ', $name, 3);
                if ((!$this->_contactExists) || ($this->_contactExists && $this->isUpdateable('contacts_contact_name_update'))) {
                    $customer->setFirstname($nameParts[0]);
                    if (count($nameParts) == 3) {
                        $customer->setMiddlename($nameParts[1]);
                        $customer->setLastname($nameParts[2]);
                    } else {
                        $customer->setLastname($nameParts[1]);
                    }
                }
                if ($erpCustomer->isTypeSupplier()) {
                } else if ($erpCustomer->isTypeCustomer()) {
                    throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ACCOUNT_CODE, $erpCustomer->getShortCode()), self::STATUS_INVALID_ACCOUNT_CODE);
                }
                if ($new) {
                    $newLinkToken = $this->mathRandom->getUniqueHash();
                    $customer->setRpToken($newLinkToken);
                    $customer->setRpTokenCreatedAt(
                        $this->dateTimeDateTimeFactory->create()->date('Y-m-d H:i:s')
                    );
                    $customer->setPassword($this->mathRandom->getRandomString(10));
                }
                $customer->save();
                $this->saveCustomerInformation($customer,$erpCustomer->getId(),$contact);
                if ($new && $this->getConfigFlag('send_emails')) {
                    $customer->sendNewAccountEmail();
                }
            }
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
            // load by previous supplier erp account code as supplie rno longer exists
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->addAttributeToFilter('ecc_contact_code', $contactCode);
            $collection->addAttributeToFilter('ecc_prev_supplier_erpaccount', $accountNumber);
            $collection->addFieldToFilter('website_id', $website);
            $customer = $collection->getFirstItem();
        } else {
            // load by supplier erp account id
            $collection = $this->customerResourceModelCustomerCollectionFactory->create();
            $collection->addAttributeToFilter('ecc_contact_code', $contactCode);
            $collection->addAttributeToFilter('ecc_supplier_erpaccount_id', $erpCustomer->getId());
            $collection->addFieldToFilter('website_id', $website);
            $customer = $collection->getFirstItem();
        }

        /* @var $customer Mage_Customer_Model_Customer */

        if (!empty($customer) && !$customer->isObjectNew()) {
            $customer->delete();
        }
    }

    /**
     * Works out which websites to assign the contact to based on config / branding.
     *
     * @return array()
     */
    private function _getWebsites()
    {

        // check the config for the customer scope
        // 1 - customers per website
        // 0 - global customers

        if ($this->scopeConfig->isSetFlag('customer/account_share/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {


            $stores = $this->_loadStores($this->getRequest());

            $websites = array();

            foreach ($stores as $store) {
                /* @var $store Mage_Core_Model_Store */
                if (!in_array($store->getWebsiteId(), $websites)) {
                    $websites[] = $store->getWebsiteId();
                }
            }

            if (empty($websites)) {
                //M1 > M2 Translation Begin (Rule p2-6.5)
                //$websites = array(Mage::app()->getDefaultStoreView()->getWebsiteId());
                $websites = array($this->storeManager->getDefaultStoreView()->getWebsiteId());
                //M1 > M2 Translation End
            }
        } else {
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$websites = array(Mage::app()->getDefaultStoreView()->getWebsiteId());
            $websites = array($this->storeManager->getDefaultStoreView()->getWebsiteId());
            //M1 > M2 Translation End
        }

        return $websites;
    }

    private function updateContactField($field, $value, $config, $customer)
    {
        if ((!$this->_contactExists) || ($this->_contactExists && $this->isUpdateable($config))) {
            $customer->setData($field, $value);
        }
    }



    public function saveCustomerInformation($customer,$erpAccountId,$contact) {
        $contactCode = $this->getVarienData('contact_code', $contact);
        $function = $this->getVarienData('contact_function', $contact);
        $telephoneNumber = $this->getVarienData('contact_telephone', $contact);
        $faxNumber = $this->getVarienData('contact_fax', $contact);
        $mobileNumber = $contact->getMobileNumber();
        $loginId = $this->getVarienData('contact_login', $contact);
        $customerRepository = $this->customerRepository->getById($customer->getId());
        if ((!$this->_contactExists) || ($this->_contactExists && $this->isUpdateable('contacts_contact_telephone_number_update'))) {
            $customerRepository->setCustomAttribute('ecc_telephone_number', $telephoneNumber);
        }
        if ((!$this->_contactExists) || ($this->_contactExists && $this->isUpdateable('contacts_contact_function_update'))) {
            $customerRepository->setCustomAttribute('ecc_function', $function);
        }
        if ((!$this->_contactExists) || ($this->_contactExists && $this->isUpdateable('contacts_contact_fax_number_update'))) {
            $customerRepository->setCustomAttribute('ecc_fax_number', $faxNumber);
        }
        if ((!$this->_contactExists) || ($this->_contactExists && $this->isUpdateable('contacts_contact_mobile_number_update'))) {
            $customerRepository->setCustomAttribute('ecc_mobile_number', $mobileNumber);
        }
        $customerRepository->setCustomAttribute('ecc_supplier_erpaccount_id', $erpAccountId);
        $customerRepository->setCustomAttribute('ecc_erpaccount_id', $erpAccountId);
        $customerRepository->setCustomAttribute('ecc_contact_code',  $contactCode);
        $customerRepository->setCustomAttribute('ecc_erp_account_type', 'supplier');
        $customerRepository->setCustomAttribute('ecc_erp_login_id', $loginId);
        $this->customerRepository->save($customerRepository);
    }

}