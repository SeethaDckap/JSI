<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper\Messaging;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Epicor\Comm\Model\MinOrderAmountFlag;

/**
 * Description of Customer
 *
 * @author David.Wylie
 */
class Customer extends \Epicor\Comm\Helper\Messaging
{

    const XML_PATH_SHOPPER_DEFAULT_ADDRESSES = 'epicor_comm_field_mapping/cus_mapping/shopper_default_addresses';

    /**
     * @var \Epicor\Comm\Model\Message\Upload 
     */
    protected $_uploadModel;
    protected $_accountUpdate = false;
    protected $_stores = [];
    protected $_allowedContractParams = array("H", "B", "N");
    protected $_requiredContractParams = array("H", "E", "O");
    protected $_allowNonContractParams = array("T", "F");

    /**
     * @var \Magento\Tax\Model\ClassModelFactory
     */
    protected $taxClassModelFactory;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $customerGroupFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $customerResourceModelGroupCollectionFactory;

    /**
     * @var \Epicor\B2b\Helper\Data
     */
    protected $b2bHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\Store\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressStoreCollectionFactory;

    /**
     * @var \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory
     */
    protected $taxCollectionFactory;
    
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    private $minOrderAmountFlag;

    public function __construct(
        MinOrderAmountFlag $minOrderAmountFlag,
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Magento\Tax\Model\ClassModelFactory $taxClassModelFactory,
        \Magento\Customer\Model\GroupFactory $customerGroupFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerResourceModelGroupCollectionFactory,
        \Epicor\B2b\Helper\Data $b2bHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\Store\CollectionFactory $commResourceCustomerErpaccountAddressStoreCollectionFactory,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->taxCollectionFactory = $taxCollectionFactory;
        $this->taxClassModelFactory = $taxClassModelFactory;
        $this->customerGroupFactory = $customerGroupFactory;
        $this->customerResourceModelGroupCollectionFactory = $customerResourceModelGroupCollectionFactory;
        $this->b2bHelper = $b2bHelper;
        $this->commResourceCustomerErpaccountAddressStoreCollectionFactory = $commResourceCustomerErpaccountAddressStoreCollectionFactory;
        $this->cacheTypeList = $cacheTypeList;
        
        parent::__construct($context);
        $this->minOrderAmountFlag = $minOrderAmountFlag;
    }

    public function combineAddressCountry($rawCountry, $configBase)
    {
        return $this->getCountryCodeMapping($erpCountryCode, self::ERP_TO_MAGENTO);
    }

    public function getCustomerAddresses($customer)
    {
        $customer_erp_address = array();
        $tmp_addresses = $customer->getAddressesCollection();
        foreach ($tmp_addresses as $address) {
            $erp_address_code = $address->getEccErpAddressCode();
            $erp_group_code = $address->getEccErpGroupCode();
            if (!empty($erp_address_code))
                $customer_erp_address[$erp_group_code . '-' . $erp_address_code] = $address;
        }
        return $customer_erp_address;
    }

    public function getErpAddresses($customer, $type = '', $erpAccountId = null)
    {
        $accountId = $erpAccountId ? $erpAccountId : $customer->getEccErpaccountId();
        $erp_group = $this->commCustomerErpaccountFactory->create()->load($accountId);

        $erp_group_addresses = array();
        $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()
            ->addFilter('erp_customer_group_code', $erp_group->getErpCode());

        if ($type != '') {
            $collection->addFilter('is_' . $type, '1');
        }

        $tmp_erp_group_addresses = $collection->load();
        foreach ($tmp_erp_group_addresses as $address) {
            $erp_address_code = $address->getErpCode();
            $erp_group_code = $address->getErpCustomerGroupCode();
            $erp_group_addresses[$erp_group_code . '-' . $erp_address_code] = $address;
        }

        return $erp_group_addresses;
    }

    /**
     * Returns an address to use as the customers delivery address
     * 
     * @param boolean $customerFirst
     * 
     * @return \Magento\Customer\Model\Address
     */
    public function getCustomerDeliveryAddress($customerFirst = false)
    {
        $customer = $this->customerSession->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */

        $account = $this->getErpAccountInfo();
        /* @var $account Epicor_Comm_Model_Customer_Erpaccount */

        // load the customer address that matches the ERP Default delivery Code
        $addressColl = $customer->getAddressCollection()
            ->setCustomerFilter($customer)
            ->addAttributeToSelect('*');

        $addressColl->addAttributeToFilter('ecc_erp_address_code', $account->getDefaultDeliveryAddressCode());

        $erpShippingAddress = $addressColl->getFirstItem();

        // load the customer default address
        $cusShippingAddress = $customer->getDefaultShippingAddress();

        if ($customerFirst) {
            $shippingAddress = $cusShippingAddress;
            $data = $shippingAddress->getData();
            if (empty($data)) {
                $shippingAddress = $erpShippingAddress;
            }
        } else {
            $shippingAddress = $erpShippingAddress;
            $data = $shippingAddress->getData();
            if (empty($data)) {
                $shippingAddress = $cusShippingAddress;
            }
        }

        return $shippingAddress;
    }

    /**
     * Converts an address to an array in the format widely used by messages for xml generation
     * 
     * @param \Magento\Customer\Model\Address $address
     * @param boolean $carriageText
     * 
     * @return array
     * 
     */
    public function formatCustomerAddress($address, $carriageText = false, $addEmail = false)
    {


        $addressData = array();
        if ($address) {
            if ($address instanceof \Magento\Quote\Model\Quote\Address || $address instanceof \Magento\Sales\Model\Order\Address) {
                $name = $address->getName();
            } else {
                if ($address->getCustomer()) {
                    $name = $address->getCustomer()->getName();
                } else {
                    $name = $address->getName();
                }
            }

            $aData = $address->getData();

            if (!empty($aData)) {

                $addressData = array(
                    'addressCode' => $address->getEccErpAddressCode(),
                    'contactName' => $this->stripNonPrintableChars($name),
                    'name' => $this->stripNonPrintableChars($address->getCompany()),
                    'address1' => $this->stripNonPrintableChars($address->getStreet1()),
                    'address2' => $this->stripNonPrintableChars($address->getStreet2()),
                    'address3' => $this->stripNonPrintableChars($address->getStreet3()),
                    'city' => $this->stripNonPrintableChars($address->getCity()),
//                    'county' => $this->stripNonPrintableChars($address->getRegion()),
                    'county' => $this->stripNonPrintableChars($this->commMessagingHelper->getRegionNameOrCode($address->getCountryId(), $address->getRegion())),
                    'country' => $this->getErpCountryCode($address->getCountryId()),
                    'postcode' => $this->stripNonPrintableChars($address->getPostcode()),
                    'telephoneNumber' => $this->stripNonPrintableChars($address->getTelephone()),
                    'mobileNumber' => $this->stripNonPrintableChars($address->getEccMobileNumber()),
                    'faxNumber' => $this->stripNonPrintableChars($address->getFax())
                );

                if ($carriageText) {
                    $addressData['carriageText'] = $address->getCarriageText();
                }

                if ($addEmail) {
                    $addressData['emailAddress'] = $address->getEccEmail();
                }
            }
        }

        return $addressData;
    }

    /**
     * 
     * @param string $erpCode
     * @param string $erpCustomerGroupCode
     * @param string $type
     * @return \Epicor\Comm\Model\Customer\Erpaccount\Address
     */
    public function getErpAddress($erpCode, $erpCustomerGroupCode, $type = null)
    {
        $address_model = $this->commCustomerErpaccountAddressFactory->create();
        if (is_null($type))
            $type = $address_model::ADDRESS_DEFAULT;

        $erpCustomerGroupAddressColl = $address_model->getCollection();
        if ($type != $address_model::ADDRESS_REGISTERED)
            $erpCustomerGroupAddressColl->addFieldToFilter('erp_code', $erpCode);
        else
            $erpCustomerGroupAddressColl->addFieldToFilter('is_' . $type, '1');

        $erpCustomerGroupAddressColl->addFieldToFilter('erp_customer_group_code', $erpCustomerGroupCode);
        $erpCustomerGroupAddress = $erpCustomerGroupAddressColl->getFirstItem();

        $erpCustomerGroupAddress->setErpCode($erpCode);

        if ($erpCustomerGroupAddress->isObjectNew()) {
            $erpCustomerGroupAddress = $address_model;
            $erpCustomerGroupAddress->setErpCustomerGroupCode($erpCustomerGroupCode);
            $erpCustomerGroupAddress->setErpCode($erpCode);
            $erpCustomerGroupAddress->setData('is_' . $type, 1);
        }
        return $erpCustomerGroupAddress;
    }

    /**
     * Adds / edits / removes customers based on the XML model passed
     * 
     * Used by CUS and CNC messages
     * 
     * @param \Epicor\Common\Model\Xmlvarien $customer
     * @param string $configBase
     * 
     * @throws \Exception
     */
    public function processCustomerAction($customer, $configBase)
    {
        $this->_uploadModel = $message = $this->commMessageUploadFactory->create();
        /* @var $this->_uploadModel Epicor_Comm_Model_Message_Upload */
        $this->_uploadModel->setConfigBase($configBase);
        $this->_uploadModel->setErpData($customer);

        $accountCode = $this->_uploadModel->getVarienData('customer_account_code', $customer);

        $att = $customer->getData('_attributes');
        $rawType = is_object($att) ? $att->getType() : '';

        if (empty($rawType)) {
            $rawType = 'B';
        }
//        if (!in_array($rawType, array('B', 'C' ))) {
//            $error = \Epicor\Comm\Model\Message::STATUS_INVALID_CUSTOMER_TYPE;
//            throw new \Exception(
//            $this->_uploadModel->getErrorDescription(
//                $error, $rawType
//            ), $error
//            );
//        }
//        
//          $accountType = $rawType == 'C' ? 'B2C' : 'B2B';
        
        if (!in_array($rawType, array('B', 'C' ,'R','D' ))) {
            $error = \Epicor\Comm\Model\Message::STATUS_INVALID_CUSTOMER_TYPE;
            throw new \Exception(
            $this->_uploadModel->getErrorDescription(
                $error, $rawType
            ), $error
            );
        }
        switch ($rawType) {
             case 'C':
                $accountType =  'B2C';
                break;
             case 'B':
                $accountType =  'B2B';
                break;
             case 'R':
                $accountType =  'Dealer';
                break;
             case 'D':
               $accountType =  'Distributor';
                break;
            default:
                $accountType =  'B2B';
        }
       
      

        $helper = $this->commonXmlHelper;

        if (!empty($accountCode)) {

            $consumerLicense = $helper->isLicensedFor(array('Consumer'));
            $customerLicense = $helper->isLicensedFor(array('Customer'));
            $dealerLicense = $helper->isLicensedFor(array('Dealer_Portal'));
            
            $stores = $this->_loadStores($customer);

            if ($consumerLicense && !$customerLicense) {
                $found = false;
                foreach ($stores as $store) {
                    if ($customer->getAccountId() == $this->scopeConfig->getValue('customer/create_account/qs_default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)) {
                        $found = true;
                        break;
                    }
                }

                if ($found == false && $accountType == 'B2B') {
                    throw new \Exception(
                    'Licensing does not allow non-default B2B ERP Accounts', \Epicor\Comm\Model\Message::STATUS_ERP_LICENSE_REQUIRED
                    );
                }
            }
            
            
            if(!$dealerLicense) {
                $found = false;
                $dealerPortal = array('Dealer','Distributor');
                if (in_array($accountType, $dealerPortal)) {
                    throw new \Exception(
                    'Licensing does not allow ' .$accountType.' ERP Accounts', \Epicor\Comm\Model\Message::STATUS_ERP_LICENSE_REQUIRED
                    );
                }                
            }            

            $deleteFlag = $this->_uploadModel->getVarienDataFlag('customer_delete', $customer);

            if ($deleteFlag) {
                if ($this->_uploadModel->isUpdateable('customer_delete_update')) {
                    $this->deleteCustomer($accountCode);
                }
            } else {

                $brands = $helper->varienToArray($customer->getVarienDataArrayFromPath('brands/brand'));

                $erpCustomer = $this->_uploadModel->getErpAccount($accountCode);
                $account = $customer->getData('account');
                $minOrderAmount = $account->getData('min_order_value');
                $this->minOrderAmountFlag->setMinOrderFlagFromCusConfig($erpCustomer);

                if (!$erpCustomer->isObjectNew()) {
                    $this->_accountUpdate = true;
                }

                $erpCustomer->setAccountType($accountType);

                $erpCustomer = $this->combineErpData($customer, $erpCustomer);

                $address_model = $this->commCustomerErpaccountAddressFactory->create();

                $this->processAddress($erpCustomer, $address_model::ADDRESS_BILLING, true, $stores, $brands);
                $this->processAddress($erpCustomer, $address_model::ADDRESS_SHIPPING, true, $stores, $brands);
                $this->processAddress($erpCustomer, $address_model::ADDRESS_REGISTERED, false, $stores, $brands);

                $this->processParents($customer, $erpCustomer);

                $erpCustomer->setBrands(serialize($brands));
                $erpCustomer->setBrandRefresh(false);
                $erpCustomer->setNewStores($stores);
                $this->setPreRegPassword($erpCustomer);

                $this->processSettings($customer, $erpCustomer);
                $this->processLocations($customer, $erpCustomer);
                $this->processContracts($customer, $erpCustomer);
                $this->branchPickupMessaging($customer, $erpCustomer);

                $erpCustomer->save();

                // check default erp account for default store
                $cacheClean = false;
                if ($erpCustomer->getCompany() == $this->scopeConfig->getValue('Epicor_Comm/licensing/company', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0) &&
                    $erpCustomer->getShortCode() == $this->scopeConfig->getValue('customer/create_account/qs_default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0)) {
                    //M1 > M2 Translation Begin (Rule P2-2)
                    //Mage::getConfig()->init()->saveConfig('customer/create_account/default_erpaccount', $erpCustomer->getId());
                    //Mage::getConfig()->init()->saveConfig('customer/create_account/default_erpaccount_name', $erpCustomer->getName());
                    $this->resourceConfig->saveConfig('customer/create_account/default_erpaccount', $erpCustomer->getId(), \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                    $this->resourceConfig->saveConfig('customer/create_account/default_erpaccount_name', $erpCustomer->getName(), \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                    //M1 > M2 Translation End
                    $cacheClean = true;
                }
                // check default erp account for each store
                $globalDefaultErpAccount = $this->scopeConfig->getValue('customer/create_account/qs_default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
                foreach ($stores as $store) {
                    $storeDefaultErpAccount = $this->scopeConfig->getValue('customer/create_account/qs_default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
                    if ($globalDefaultErpAccount != $storeDefaultErpAccount) {
                        if ($erpCustomer->getCompany() == $this->scopeConfig->getValue('epicor_comm/licensing/company', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store) &&
                            $erpCustomer->getShortCode() == $this->scopeConfig->getValue('customer/create_account/qs_default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store)) {
                            //M1 > M2 Translation Begin (Rule P2-2)
                            //Mage::getConfig()->init()->saveConfig('customer/create_account/default_erpaccount', $erpCustomer->getId(), 'stores', $store);
                            $this->resourceConfig->saveConfig('customer/create_account/default_erpaccount', $erpCustomer->getId(), 'stores', $store);
                            //M1 > M2 Translation End
                            $cacheClean = true;
                        }
                    }
                }

                if ($cacheClean) {
                    $this->cacheTypeList->cleanType('config');
                }

                return $erpCustomer;
            }
        } else {
            throw new \Exception($this->_uploadModel->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_INVALID_ACCOUNT_CODE), \Epicor\Comm\Model\Message::STATUS_INVALID_ACCOUNT_CODE);
        }
    }

    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    public function branchPickupMessaging($erpData, $erpCustomerGroup)
    {
        $erpAccounts = $erpData->getAccount();
        $accountVals = $erpAccounts->getIsBranchPickupAllowed();
        $getBranchPickupAllowed = ($accountVals == '0') ? 'false' : $accountVals;
        if ($this->_uploadModel->isUpdateable('cus_branch_pickup', $this->_accountUpdate) && $getBranchPickupAllowed) {
            if (in_array($getBranchPickupAllowed, array('true', '1', 'Y'))) {
                $erpCustomerGroup->setIsBranchPickupAllowed(1);
            } elseif (in_array($getBranchPickupAllowed, array('false', '0', 'N'))) {
                $erpCustomerGroup->setIsBranchPickupAllowed(0);
            } elseif ($getBranchPickupAllowed == "global") {
                $erpCustomerGroup->setIsBranchPickupAllowed(2);
            }
        }
    }

    /**
     * Loads the stores from the brands provided
     */
    public function _loadStores($erpData)
    {
        $brands = $erpData->getVarienDataArrayFromPath('brands/brand');
        $stores = array();
        if (!is_array($brands)) {
            $brands = array($brands);
        }

        if (!empty($brands)) {

            if (!is_array($brands)) {
                $brands = array($brands);
            }

            foreach ($brands as $brand) {
                $brandStores = $this->getStoreFromBranding($brand->getCompany(), $brand->getSite(), $brand->getWarehouse(), $brand->getGroup());
                foreach ($brandStores as $store) {
                    $this->_stores[$store->getId()] = $store;
                    $stores[] = $store->getId();
                }
            }
        } else {
            $brandStores = $this->getStoreFromBranding(null);
            foreach ($brandStores as $store) {
                $this->_stores[$store->getId()] = $store;
                $stores[] = $store->getId();
            }
        }

        if (empty($stores) && $this->scopeConfig->isSetFlag('epicor_comm_field_mapping/cus_mapping/reject_none_matching_branding', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            throw new \Exception(
            'Provided Brands do not match any stores', \Epicor\Comm\Model\Message::STATUS_GENERAL_ERROR
            );
        }

        return $stores;
    }

    /**
     * 
     * @param string $accountCode
     */
    private function deleteCustomer($accountCode)
    {
        $erpCustomer = $this->_uploadModel->getErpAccount($accountCode);
        if (!$erpCustomer->isObjectNew()) {

            $erpCustomer->delete();
        }
    }

    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    private function processParents($erpData, $erpCustomerGroup)
    {
        if ($this->_uploadModel->isUpdateable('customer_parents_update', $this->_accountUpdate)) {
            $erpParents = (!$erpCustomerGroup->isObjectNew()) ? $erpCustomerGroup->getParents() : array();

            $parentsGroup = $erpData->getParents();
            $parents = array();

            if (is_object($parentsGroup)) {
                $parents = $parentsGroup->getasarrayParent();
            }

            $types = array();

            if ($parents) {
                $typesAllowed = \Epicor\Comm\Model\Erp\Customer\Group\Hierarchy::$linkTypes;

                foreach ($parents as $parent) {
                    $att = $parent->getData('_attributes');
                    if (is_object($att)) {

                        $type = $att->getType();
                        $code = $parent->getValue();

                        if (isset($types[$type])) {
                            $error = \Epicor\Comm\Model\Message::STATUS_INVALID_CUSTOMER_PARENT_TYPE;
                            throw new \Exception(
                            $this->_uploadModel->getErrorDescription(
                                $error, $type, 'Only 1 allowed per type'
                            ), $error
                            );
                        }

                        if (!in_array($type, array_keys($typesAllowed))) {
                            $error = \Epicor\Comm\Model\Message::STATUS_INVALID_CUSTOMER_PARENT_TYPE;
                            throw new \Exception(
                            $this->_uploadModel->getErrorDescription(
                                $error, $type, 'Unknown Type'
                            ), $error
                            );
                        }

                        $parentAccount = $this->_loadOtherErpAccount($code, $erpCustomerGroup);
                        if (!$parentAccount->isObjectNew()) {
                            $existingType = isset($erpParents[$type]) ? $erpParents[$type] : false;
                            if (!$existingType || $existingType->getId() != $parentAccount->getId()) {
                                $erpCustomerGroup->addParent($parentAccount->getId(), $type);
                            }
                        } else {
                            $error = \Epicor\Comm\Model\Message::STATUS_CUSTOMER_PARENT_NOT_FOUND;
                            throw new \Exception(
                            $this->_uploadModel->getErrorDescription(
                                $error, $code
                            ), $error
                            );
                        }

                        $types[$type] = $parentAccount->getId();
                    }
                }
            }

            foreach ($erpParents as $type => $parent) {
                if (!isset($types[$type]) || $types[$type] != $parent->getId()) {
                    $erpCustomerGroup->removeParent($parent->getId(), $type);
                }
            }
        }
    }

    /**
     * Loads an erp account by code from the same company
     * 
     * @param string $code
     * 
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    private function _loadOtherErpAccount($code, $erpCustomerGroup)
    {
        $accountCode = $erpCustomerGroup->getCompany() . $this->getUOMSeparator() . $code;

        $account = $this->commCustomerErpaccountFactory->create();
        /* @var $account Epicor_Comm_Model_Customer_Erpaccount */

        $account->load($accountCode, 'erp_code');

        return $account;
    }

    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    private function combineErpData($erpData, $erpCustomerGroup)
    {
        $this->combineNameAndCodes($erpData, $erpCustomerGroup);
        $this->combineCurrencies($erpData, $erpCustomerGroup);
        $this->combineTax($erpData, $erpCustomerGroup);
        $this->combinePayments($erpData, $erpCustomerGroup);
        $this->combineOrderLimits($erpData, $erpCustomerGroup);
        $this->combineCustomerGroupDefaults($erpData, $erpCustomerGroup);
        $this->combineCustomerGroupDefaults($erpData, $erpCustomerGroup);
        $this->combineCustomerAllowedDeliveryMethods($erpData, $erpCustomerGroup);
        $this->combineCustomerAllowedPaymentMethods($erpData, $erpCustomerGroup);
        $this->combineCustomerCentralCollection($erpData, $erpCustomerGroup);
        return $erpCustomerGroup;
    }

    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    private function combineNameAndCodes($erpData, $erpCustomerGroup)
    {
        $erpCode = $erpData->getAccountNumber(); #->getVarienData('customer_account_code', $erpData);
        if (!is_null($erpCode))
            $erpCustomerGroup->setErpCode($erpCode);

        $shortCode = $erpData->getAccountId();
        if (!is_null($shortCode))
            $erpCustomerGroup->setShortCode($shortCode);

        $accountNumber = $erpData->getOrigAccountNumber();
        if (!is_null($accountNumber))
            $erpCustomerGroup->setAccountNumber($accountNumber);
        else
            $erpCustomerGroup->setAccountNumber($erpCode);

        $company = $erpData->getBrandCompany();
        $erpCustomerGroup->setCompany($company);

        $name = $this->_uploadModel->getVarienData('customer_account_name', $erpData);
        $this->setFieldValue('name', $name, 'customer_account_name_update', $erpCustomerGroup);

        $email = $this->_uploadModel->getVarienData('customer_emailaddress', $erpData);
        $this->setFieldValue('email', $email, 'customer_account_email_address_update', $erpCustomerGroup);

        /*
         * NB the below registration_email hasn't been tested, as it isn't currently used anywhere (CNC uses account->email() as registration email address)
         * NB the below sales_rep hasn't been tested, as it isn't currently used anywhere 
         */
        $registration_email = $this->_uploadModel->getVarienData('default_registration_email_address', $erpData);
        $this->setFieldValue('registration_email_address', $registration_email, 'customer_defaults_registration_email_address_update', $erpCustomerGroup);

        $salesRep = $this->_uploadModel->getVarienData('customer_salesrep', $erpData);
        $this->setFieldValue('sales_rep', $salesRep, 'customer_account_sales_rep_update', $erpCustomerGroup);
    }

    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    private function combineCurrencies($erpData, $erpCustomerGroup)
    {
        $onStop = $this->_uploadModel->getVarienDataFlag('customer_onstop', $erpData);
        $balance = $this->_uploadModel->getVarienData('customer_balance', $erpData) ? $this->_uploadModel->getVarienData('customer_balance', $erpData) : 0;
        $creditLimit = $this->_uploadModel->getVarienData('customer_creditlimit', $erpData) ? $this->_uploadModel->getVarienData('customer_creditlimit', $erpData) : 0;
        $unAllocatedCash = $this->_uploadModel->getVarienData('customer_unallocatedcash', $erpData) ? $this->_uploadModel->getVarienData('customer_unallocatedcash', $erpData) : 0;
        $minOrderAmount = $erpData->getAccount()->getMinOrderValue() ? $erpData->getAccount()->getMinOrderValue() : 0;
        $currencyCode = $this->_uploadModel->getVarienDataWithDefaultConfig('customer_currency_code', $erpData, 'customer_currency_code_default');
        $currencyCode = $this->getCurrencyMapping($currencyCode, self::ERP_TO_MAGENTO);

        if (!$this->isCurrencyCodeValid($currencyCode))
            throw new \Exception($this->_uploadModel->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_GENERAL_ERROR, 'Currency Code ' . $currencyCode . ' is invalid'), \Epicor\Comm\Model\Message::STATUS_GENERAL_ERROR);

        $currentCurrencies = $erpCustomerGroup->getAllCurrencyData();

        $erpCustomerGroup->setDefaultCurrencyCode($currencyCode);
        $erpCustomerGroup->addCurrency($currencyCode);
        $erpCustomerGroup->setIsDefault(true);
        $erpCustomerGroup->setOnstop($onStop, $currencyCode);
        $erpCustomerGroup->setBalance($balance, $currencyCode);
        $erpCustomerGroup->setCreditLimit($creditLimit, $currencyCode);
        $erpCustomerGroup->setUnallocatedCash($unAllocatedCash, $currencyCode);
        if ($this->minOrderAmountFlag->isMinOrderSetToUploadInCus()) {
            $erpCustomerGroup->setMinOrderAmount($minOrderAmount, $currencyCode);
        }

        $currencyCodes = (array) $this->_uploadModel->getVarienDataArray('customer_currencies', $erpData);
        foreach ($currencyCodes as $currency_code) {
            $code = $this->_uploadModel->getVarienData('customer_currencies_currency_code', $currency_code);
            $code = $this->getCurrencyMapping($code, self::ERP_TO_MAGENTO);

            if ($this->isCurrencyCodeValid($code)) {
                $erpCustomerGroup->addCurrency($code);
                if (isset($currentCurrencies[$code])) {
                    unset($currentCurrencies[$code]);
                }
            } else {
                throw new \Exception($this->_uploadModel->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_GENERAL_ERROR, 'Currency Code ' . $code . ' is invalid'), \Epicor\Comm\Model\Message::STATUS_GENERAL_ERROR);
            }
        }

        if (!empty($currentCurrencies)) {
            foreach ($currentCurrencies as $oldCode => $data) {
                $erpCustomerGroup->removeCurrency($oldCode);
            }
        }
    }

    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    private function combineTax($erpData, $erpCustomerGroup)
    {
        // set up Tax Class

        $TaxClassName = '';
        $taxCode = $this->_uploadModel->getVarienDataWithDefaultConfig('customer_taxcode', $erpData, 'customer_taxcode_default');
        if (!empty($taxCode)) {
            $TaxClassName = $taxCode;
        }

        $this->setErpCustomerTaxCode($TaxClassName, $erpCustomerGroup);
    }

    private function setErpCustomerTaxCode($TaxClassName, $erpCustomerGroup)
    {
        if (!$this->_accountUpdate || $this->_accountUpdate && $this->_uploadModel->isUpdateable('customer_tax_code_update')) {   // if account exists
            // find taxClassId for given rate

            /* @var $taxClass Mage_Tax_Model_Class */
            /* @var $taxClass Mage_Customer_Model_Group */

            //M1 > M2 Translation Begin (Rule p2-5.2)
            /*$taxClassColl = Mage::getResourceModel('tax/class_collection')
                ->addFieldToFilter('class_name', $TaxClassName)
                ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
                ->addFieldToSelect('class_id')
                ->setPageSize(1);*/
            $taxClassColl = $this->taxCollectionFactory->create()
                ->addFieldToFilter('class_name', $TaxClassName)
                ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
                ->addFieldToSelect('class_id')
                ->setPageSize(1);
            //M1 > M2 Translation End
            if (!empty($taxClassColl) && (count($taxClassColl) > 0)) {
                $taxClass = $taxClassColl->getFirstItem();
            } else {
                //if tax class doesn't exist, create it and save to erp customer group under the new column 
                try {
                    //create tax class
                    $taxClass = $this->taxClassModelFactory->create();
                    $taxClass->setClassName($TaxClassName);
                    $taxClass->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER);
                    $taxClass->save();
                } catch (Exception $ex) {
                    throw new \Exception($this->_uploadModel->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_ERROR_CREATING_TAX_CLASS, 'Customer', $TaxClassName), \Epicor\Comm\Model\Message::STATUS_ERROR_CREATING_TAX_CLASS);
                }
            }
            $updateGroup = true;
            if ($this->scopeConfig->getValue('epicor_comm_field_mapping/cus_mapping/customer_use_multiple_customer_groups', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                $group = $this->customerGroupFactory->create()->load($TaxClassName, 'customer_group_code');
                if ($group->isObjectNew()) {
                    $group->setTaxClassId($taxClass->getId());
                    $group->setCustomerGroupCode($TaxClassName);
                    $group->save();
                }
            } else {

                $updateGroup = (!$this->_accountUpdate || $this->_accountUpdate && $this->_uploadModel->isUpdateable('customer_group_update'));

                if ($updateGroup) {
                    // if multiple customer groups not allowed, use default
                    $defaultCustomerGroup = $this->scopeConfig->getValue('epicor_comm_field_mapping/cus_mapping/customer_default_customer_group', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                    $group = $this->customerGroupFactory->create()->load($defaultCustomerGroup);

                    if ($group->isObjectNew()) {
                        $group = $this->customerResourceModelGroupCollectionFactory->create()->setRealGroupsFilter()->getFirstItem();
                    }
                }
            }

            if ($updateGroup) {
                $erpCustomerGroup->setMagentoId($group->getId());
            }
            $erpCustomerGroup->setTaxClass($taxClass->getClassName());
        }
    }

    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    private function combinePayments($erpData, $erpCustomerGroup)
    {
//        $onStop = $this->_uploadModel->getVarienDataFlagWithDefaultConfig('customer_onstop', $erpData, 'customer_onstop_default');
        $onStop = $this->_uploadModel->getVarienData('customer_onstop', $erpData);
        $this->setFieldValue('onstop', $onStop, 'customer_account_onstop_update', $erpCustomerGroup);

//        $erpCustomerGroup->setOnstop($onStop);

        $balance = $this->_uploadModel->getVarienDataWithDefaultConfig('customer_balance', $erpData, 'customer_balance_default');
        $this->setFieldValue('balance', $balance, 'customer_account_balance_update', $erpCustomerGroup);

        $creditLimit = $this->_uploadModel->getVarienDataWithDefaultConfig('customer_creditlimit', $erpData, 'customer_creditlimit_default');
        $this->setFieldValue('credit_limit', $creditLimit, 'customer_account_credit_limit_update', $erpCustomerGroup);

        $unallocatedCash = $this->_uploadModel->getVarienDataWithDefaultConfig('customer_unallocatedcash', $erpData, 'customer_unallocatedcash_default');
        $this->setFieldValue('unallocated_cash', $unallocatedCash, 'customer_account_unallocated_cash_update', $erpCustomerGroup);

        $minOrderAmount = $this->_uploadModel->getVarienData('customer_min_order_amount', $erpData);
        $this->setFieldValue('min_order_amount', $minOrderAmount, 'customer_account_min_order_value_update', $erpCustomerGroup);
    }

    /**
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    private function combineOrderLimits($erpData, $erpCustomerGroup)
    {
        $backOrders = $this->_uploadModel->getVarienDataFlagWithDefaultConfig('customer_allowbackorders', $erpData, 'customer_allowbackorders_default');

        $this->setFieldValue('allow_backorders', $backOrders, 'customer_allow_back_orders_update', $erpCustomerGroup);
    }

    private function combineCustomerGroupDefaults($erpData, $erpCustomerGroup)
    {
        if ($erpData->getDefaults()) {
            $deliveryMethodCode = $erpData->getDefaults()->getDeliveryMethodCode();
            $paymentMethodCode = $erpData->getDefaults()->getPaymentMethodCode();
            $isPoMandatory = $erpData->getDefaults()->getIsPoMandatory();
            if (!empty($isPoMandatory)) {
                $poMandatory = ($isPoMandatory == "Y") ? 1 : 0;
            } else {
                $poMandatory = null;
            }
        }
        $this->setFieldValue('default_delivery_method_code', $deliveryMethodCode, 'customer_defaults_delivery_method_code_update', $erpCustomerGroup);
        $this->setFieldValue('default_payment_method_code', $paymentMethodCode, 'customer_defaults_payment_method_code_update', $erpCustomerGroup);
        $this->setFieldValue('po_mandatory', $poMandatory, 'po_mandatory', $erpCustomerGroup);
    }

    //    private function combineCustomerGroupDefaults($erpData, $erpCustomerGroup) {
//        $currency = $this->getVarienDataWithDefaultConfig('customer_currencyCode', $erpData, 'customer_currencyCode_default');
//        $erpCustomerGroup->setCustomerCurrencyCode($currency);
//
//        $deliveryMethodCode = $this->getVarienData('default_delivery_method', $erpData);
//        if (!is_null($deliveryMethodCode))
//            $erpCustomerGroup->setDefaultDeliveryMethod($deliveryMethodCode);
//
//        $defaultDeliveryAddress = $this->getVarienData('default_delivery_address_code', $erpData);
//        if (!is_null($defaultDeliveryAddress))
//            $erpCustomerGroup->setDefaultDeliveryAddressCode($defaultDeliveryAddress);
//        
//
//        $defaultInvoiceAddress = $this->getVarienData('default_invoice_address_code', $erpData);
//        if (!is_null($defaultInvoiceAddress)) 
//            $erpCustomerGroup->setDefaultInvoiceAddressCode($defaultInvoiceAddress);
//        
//    }

    /**
     * Uses B2b to set the pre-reg password for this erp account
     * 
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    private function setPreRegPassword($erpCustomerGroup)
    {
        $this->b2bHelper->setPreregPassword($erpCustomerGroup);
    }

    /**
     * 
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomer
     * @param string $type
     * @return \Epicor\Comm\Model\Customer\Erpaccount\Address
     */
    private function processAddress($erpCustomer, $type, $default = false, $stores = array(), $brands = array())
    {
        $erpData = $this->_uploadModel->getVarienData('customer_' . $type . '_address');
        // process ERP address
        $helper = $this;
        /* @var $helper \Epicor\Comm\Helper\Messaging\Customer */
        $address_code = $this->_uploadModel->getVarienData('customer_' . $type . '_address_code', $erpData);
        $address_name = $this->_uploadModel->getVarienData('customer_' . $type . '_address_name', $erpData);
        $address_line1 = $this->_uploadModel->getVarienData('customer_' . $type . '_address_line1', $erpData);
        $address_line2 = $this->_uploadModel->getVarienData('customer_' . $type . '_address_line2', $erpData);
        $address_line3 = $this->_uploadModel->getVarienData('customer_' . $type . '_address_line3', $erpData);
        $address_city = $this->_uploadModel->getVarienData('customer_' . $type . '_address_city', $erpData);
        $address_county = $this->_uploadModel->getVarienData('customer_' . $type . '_address_county', $erpData);
        $address_postcode = $this->_uploadModel->getVarienData('customer_' . $type . '_address_postcode', $erpData);
        $message_country = $this->_uploadModel->getVarienData('customer_' . $type . '_address_country', $erpData);
        $address_country = $helper->getCountryCodeMapping($message_country, $helper::ERP_TO_MAGENTO);
        $address_telephone = $this->_uploadModel->getVarienData('customer_' . $type . '_address_telephone', $erpData);
        $address_mobile = $this->_uploadModel->getVarienData('customer_' . $type . '_address_mobile', $erpData);
        $address_fax = $this->_uploadModel->getVarienData('customer_' . $type . '_address_fax', $erpData);
        $address_instructions = $this->_uploadModel->getVarienData('customer_' . $type . '_address_instructions', $erpData);
        $address_instructions = $this->_uploadModel->getVarienData('customer_' . $type . '_address_instructions', $erpData);
        $location = is_object($erpData) ? $erpData->getLocationCode() : '';

        $isLegacyErp = $helper->isLegacyErp();
        $fullAddress = (($isLegacyErp) ? '' : trim($address_code))
            . trim($address_name)
            . trim($address_line1)
            . trim($address_line2)
            . trim($address_line3)
            . trim($address_city)
            . trim($address_county)
            . trim($address_postcode)
            . trim($message_country)
            . trim($address_telephone)
            . trim($address_mobile)
            . trim($address_fax)
            . (($isLegacyErp) ? '' : trim($address_instructions));

        if (empty($fullAddress)) {

            $address_model = $this->commCustomerErpaccountAddressFactory->create();

            if ($type == $address_model::ADDRESS_SHIPPING || $type == $address_model::ADDRESS_REGISTERED) {
                if ($isLegacyErp && $default && !empty($address_code)) {
                    $erpCustomer->setData('default_' . $type . '_address_code', $address_code);
                }
                return $this;
            } else {
                $altType = $address_model::ADDRESS_REGISTERED;

                $erpData = $this->_uploadModel->getVarienData('customer_' . $altType . '_address');

                $address_code = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_code', $erpData);
                $address_name = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_name', $erpData);
                $address_line1 = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_line1', $erpData);
                $address_line2 = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_line2', $erpData);
                $address_line3 = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_line3', $erpData);
                $address_city = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_city', $erpData);
                $address_county = $this->_uploadModel->getVarienData('customer_' . $type . '_address_county', $erpData);
                $address_postcode = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_postcode', $erpData);
                $address_country = $helper->getCountryCodeMapping($this->_uploadModel->getVarienData('customer_' . $altType . '_address_country', $erpData), $helper::ERP_TO_MAGENTO);
                $address_telephone = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_telephone', $erpData);
                $address_mobile = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_mobile', $erpData);
                $address_fax = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_fax', $erpData);
                $address_instructions = $this->_uploadModel->getVarienData('customer_' . $altType . '_address_instructions', $erpData);
                $location = $erpData->getLocationCode();
            }
        }

        $address = $erpCustomer->getAddress($address_code);
        $this->validateAddressName($address_name);

        if (!$address) {
            $erpCustomer->addAddress($address_code, $type)
                ->setType($type, $address_code)
                ->setAddressName($address_name, $address_code)
                ->setAddress1($address_line1, $address_code)
                ->setAddress2($address_line2, $address_code)
                ->setAddress3($address_line3, $address_code)
                ->setCity($address_city, $address_code)
                ->setCounty($address_county, $address_code)
                ->setPostcode($address_postcode, $address_code)
                ->setCountry($address_country, $address_code)
                ->setPhone($address_telephone, $address_code)
                ->setMobileNumber($address_mobile, $address_code)
                ->setFax($address_fax, $address_code)
                ->setInstructions($address_instructions, $address_code)
                ->setAddressStores($stores, $address_code)
                ->setAddressBrands($brands, $address_code)
                ->setAddressLocationCode($location, $address_code);
        } else {

            if (($type == 'registered' && !$address->getIsRegistered()) || ($type == 'invoice' && !$address->getIsInvoice()) || ($type == 'delivery' && !$address->getIsDelivery())) {
                $erpCustomer->setType($type, $address_code);
            }

            if ($address_name != $address->getName()) {
                $erpCustomer->setAddressName($address_name, $address_code);
            }

            //M1 > M2 Translation Begin (Rule 9)
            //if ($address_line1 != $address->getAddress1()) {
            if ($address_line1 != $address->getData('address1')) {
            //M1 > M2 Translation End
                $erpCustomer->setAddress1($address_line1, $address_code);
            }
            //M1 > M2 Translation Begin (Rule 9)
            //if ($address_line2 != $address->getAddress2()) {
            if ($address_line2 != $address->getData('address2')) {
            //M1 > M2 Translation End
                $erpCustomer->setAddress2($address_line2, $address_code);
            }
            //M1 > M2 Translation Begin (Rule 9)
            //if ($address_line3 != $address->getAddress3()) {
            if ($address_line3 != $address->getData('address3')) {
            //M1 > M2 Translation End
                $erpCustomer->setAddress3($address_line3, $address_code);
            }
            if ($address_city != $address->getCity()) {
                $erpCustomer->setCity($address_city, $address_code);
            }
            if ($address_county != $address->getCounty()) {
                $erpCustomer->setCounty($address_county, $address_code);
            }
            if ($address_postcode != $address->getPostcode()) {
                $erpCustomer->setPostcode($address_postcode, $address_code);
            }
            if ($address_country != $address->getCountry()) {
                $erpCustomer->setCountry($address_country, $address_code);
            }
            if ($address_telephone != $address->getPhone()) {
                $erpCustomer->setPhone($address_telephone, $address_code);
            }
            if ($address_mobile != $address->getMobileNumber()) {
                $erpCustomer->setMobileNumber($address_mobile, $address_code);
            }
            if ($address_fax != $address->getFax()) {
                $erpCustomer->setFax($address_fax, $address_code);
            }
            if ($address_instructions != $address->getIntructions()) {
                $erpCustomer->setInstructions($address_instructions, $address_code);
            }
            if ($location != $address->getDefaultLocationCode()) {
                $erpCustomer->setAddressLocationCode($location, $address_code);
            }

            $storeCollection = $this->commResourceCustomerErpaccountAddressStoreCollectionFactory->create();
            $storeCollection->addFieldToFilter('erp_customer_group_address', $address->getId());

            $oldStores = [];
            foreach ($storeCollection->getItems() as $store) {
                $oldStores[] = $store->getStore();
            }

            sort($stores);
            sort($oldStores);
            if ($stores != $oldStores) {
                $erpCustomer->setAddressStores($stores, $address_code);
            }

            if (serialize($brands) != $address->getBrands()) {
                $erpCustomer->setAddressBrands($brands, $address_code);
            }
        }

        if ($type == 'registered') {
            foreach ($erpCustomer->getAddresses() as $code => $address) {
                if ($address_code != $code && $address->getIsRegistered()) {

                    $address = $erpCustomer->unsetType($type, $code)->getAddress($code);

                    if (!$address->getIsRegistered() &&
                        !$address->getIsInvoice() &&
                        !$address->getIsDelivery()) {
                        $erpCustomer->removeAddress($code);
                    }
                }
            }
        }

        $erpAddress = $erpCustomer->getAddress($address_code);

        $errors = $erpCustomer->getAddress($address_code)->validate();

        if ($errors !== true && !empty($errors)) {
            throw new \Exception($this->_uploadModel->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_INVALID_ADDRESS, $type, implode(' ', $errors)), \Epicor\Comm\Model\Message::STATUS_INVALID_ADDRESS);
        }

        $countryModel = $this->directoryCountryFactory->create()->loadByCode($address_country);

        if ($countryModel->getId() != $address_country && $erpAddress->getCountry() != $countryModel->getId()) {
            $erpCustomer->setCountry($countryModel->getId(), $address_code);
        }

        if (!empty($address_county)) {
            $collection = $this->directoryRegionFactory->create()->getResourceCollection()
                ->addCountryFilter($countryModel->getId())
                ->load();

            // Check to see if the country has regions, and check if it's valid
            if ($collection->count() > 0) {
                // try loading a region with the county field as the code
                $region = $this->directoryRegionFactory->create()->loadByCode($address_county, $countryModel->getId());
                /* @var $region Mage_Directory_Model_Region */

                if (!empty($region) && !$region->isObjectNew()) {
                    if ($erpAddress->getCounty() != $region->getName()) {
                        $erpCustomer->setCounty($region->getName(), $address_code);
                    }
                    if ($erpAddress->getCountyCode() != $region->getCode()) {
                        $erpCustomer->setCountyCode($region->getCode(), $address_code);
                    }
                } else {
                    // try loading a region with the county field as the name
                    $region = $this->directoryRegionFactory->create()->loadByName($address_county, $countryModel->getId());

                    if (!empty($region) && !$region->isObjectNew()) {
                        if ($erpAddress->getCounty() != $region->getName()) {
                            $erpCustomer->setCounty($region->getName(), $address_code);
                        }
                        if ($erpAddress->getCountyCode() != $region->getCode()) {
                            $erpCustomer->setCountyCode($region->getCode(), $address_code);
                        }
                    } else {
                        throw new \Exception($this->_uploadModel->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_INVALID_ADDRESS, $type, 'Invalid county'), \Epicor\Comm\Model\Message::STATUS_INVALID_ADDRESS);
                    }
                }
            }
        } else {
            $requiredStates = explode(',', $this->scopeConfig->getValue('general/region/state_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            if (in_array($countryModel->getId(), $requiredStates)) {
                throw new \Exception($this->_uploadModel->getErrorDescription(\Epicor\Comm\Model\Message::STATUS_INVALID_ADDRESS, $type, 'County is required'), \Epicor\Comm\Model\Message::STATUS_INVALID_ADDRESS);
            }
        }

        if ($default)
            $erpCustomer->setData('default_' . $type . '_address_code', $address_code);

        $this->_eventManager->dispatch('epicor_message_cus_address_processed', array('erp_account' => $erpCustomer));
        return $this;
    }

    public function formatAddress($address = null, $typeOfAddress = 'billing')
    {
        $addressData = array();
        if ($address) {

            $addressData = $address->getData();

            if (empty($addressData)) {                             // if no address data supplied use defaults 
                $customer = $this->customerSession->getCustomer();

                if ($typeOfAddress == 'billing') {
                    $address = $customer->getDefaultBillingAddress()->getData();
                } else {
                    $address = $customer->getDefaultShippingAddress()->getData();
                }
            }

            if ($address instanceof \Magento\Quote\Model\Quote\Address || $address instanceof \Magento\Sales\Model\Order\Address) {
                $name = $address->getName();
            } else {
                $name = $address->getCustomer()->getName();
            }

            if (!empty($addressData)) {
                $addressData = array(
                    'addressCode' => $address->getEccErpAddressCode(),
                    'contactName' => $this->stripNonPrintableChars($name),
                    'name' => $this->stripNonPrintableChars($address->getCompany()),
                    'address1' => $this->stripNonPrintableChars($address->getStreet1()),
                    'address2' => $this->stripNonPrintableChars($address->getStreet2()),
                    'address3' => $this->stripNonPrintableChars($address->getStreet3()),
                    'city' => $this->stripNonPrintableChars($address->getCity()),
                    'county' => $this->stripNonPrintableChars($address->getRegion()),
                    'country' => $this->getErpCountryCode($address->getCountryId()),
                    'postcode' => $this->stripNonPrintableChars($address->getPostcode()),
                    'telephoneNumber' => $this->stripNonPrintableChars($address->getTelephone()),
                    'mobileNumber' => $this->stripNonPrintableChars($address->getMobileNumber()),
                    'faxNumber' => $this->stripNonPrintableChars($address->getFax())
                );
            }
        }

        return $addressData;
    }

    /**
     * Processes settings tag
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomer
     */
    private function processSettings($erpData, &$erpCustomer)
    {
        $settings = $erpData->getSettings();

        if ($settings && is_object($settings)) {
            $taxCode = $settings->getTaxCode();
            $oldTaxCode = $this->_uploadModel->getVarienDataWithDefaultConfig('customer_taxcode', $erpData, 'customer_taxcode_default');

            if (is_null($taxCode) || $taxCode == '') {
                $taxCode = $oldTaxCode;
            }

            $this->setErpCustomerTaxCode($taxCode, $erpCustomer);

            $allowOneOffAddress = $settings->getAllowOneOffAddress();
            if (!$this->_accountUpdate || $this->_accountUpdate && $this->_uploadModel->isUpdateable('customer_one_off_address_update')) {
                if (!is_null($allowOneOffAddress)) {
                    if ($allowOneOffAddress == 'Y') {
                        $erpCustomer->setCustomAddressAllowed(true);
                    } else {
                        $erpCustomer->setCustomAddressAllowed(false);
                    }
                }
            }

            $isWarrantyCustomer = $settings->getIsWarrantyCustomer();
            if (!$this->_accountUpdate || $this->_accountUpdate && $this->_uploadModel->isUpdateable('customer_warranty_customer_update')) {
                if (!is_null($isWarrantyCustomer)) {
                    if ($isWarrantyCustomer == 'Y') {
                        $erpCustomer->setIsWarrantyCustomer(true);
                    } else {
                        $erpCustomer->setIsWarrantyCustomer(false);
                    }
                }
            }
        }
    }

    /**
     * Processes Locations tag
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomer
     */
    private function processLocations($erpData, &$erpCustomer)
    {
        if (!$this->_uploadModel->isUpdateable('customer_locations_update', $this->_accountUpdate)) {
            return;
        }

        $helper = $this->commLocationsHelper;
        /* @var $helper Epicor_Comm_Helper_Location */

        $company = $erpData->getBrandCompany();
        $defaults = $erpData->getDefaults();
        $defaultLocation = $defaults->getLocationCode();

        $_defaultLocation = $helper->checkAndCreateLocation($defaultLocation, $company, $this->_stores);
        $defaultLocation =  is_null($_defaultLocation) ? $defaultLocation : $_defaultLocation->getCode();
        $erpCustomer->setDefaultLocationCode($defaultLocation);

        $locations = $this->_uploadModel->_getGroupedData('locations', 'location_code', $erpData);

        $newLocations = array();

        $linkType = null;
        foreach ($locations as $locationCode) {
            $atts = $locationCode->getData('_attributes');
            $code = $locationCode->getValue();
            $include = ($atts) ? $atts->getInclude() : '';

            if (is_null($linkType)) {
                $linkType = $include == 'Y' ? \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE : \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE;
            }

            $_location = $helper->checkAndCreateLocation($code, $company, $this->_stores);

            $newLocations[] = is_null($_location) ? $code : $_location->getCode();
        }

        if (is_null($linkType)) {
            $linkType = \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE;
        }

        $erpCustomer->updateLocations($newLocations, $linkType, true);
    }

    /**
     * Processes Contracts tag
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomer
     */
    public function processContracts($erpData, &$erpCustomer)
    {
        $settings = $erpData->getContracts();

        if ($settings && is_object($settings)) {

            //$this->_accountUpdate -- to check Overwritten on Update
            //For allowedContractType tag
            if ($this->_uploadModel->isUpdateable('customer_allowed_contract_type', $this->_accountUpdate) && $settings->getAllowedContractType()) {
                $allowedParam = $this->_allowedContractParams;
                $allowedContractType = $settings->getAllowedContractType();
                $validate = in_array($allowedContractType, $allowedParam);
                if ($validate) {
                    $erpCustomer->setAllowedContractType($allowedContractType);
                }
            }

            //$this->_accountUpdate -- to check Overwritten on Update
            //For requiredContractType tag
            if ($this->_uploadModel->isUpdateable('customer_required_contract_type', $this->_accountUpdate) && $settings->getRequiredContractType()) {
                $allowedParam = $this->_requiredContractParams;
                $requiredContractType = $settings->getRequiredContractType();
                $validate = in_array($requiredContractType, $allowedParam);
                if ($validate) {
                    $erpCustomer->setRequiredContractType($requiredContractType);
                }
            }

            //$this->_accountUpdate -- to check Overwritten on Update
            //For allowNonContractItems tag
            if ($this->_uploadModel->isUpdateable('customer_allow_non_contract_items', $this->_accountUpdate) && $settings->getAllowNonContractItems()) {
                $allowedParam = $this->_allowNonContractParams;
                $allowedNonContractItems = $settings->getAllowNonContractItems();
                $validate = in_array($allowedNonContractItems, $allowedParam);
                if ($validate) {
                    //Convert T/F to 1/0 before setting it to the erp account
                    $convertNonContractItems = $allowedNonContractItems === 'T' ? 1 : 0;
                    $erpCustomer->setAllowNonContractItems($convertNonContractItems);
                }
            }
        }
    }


    private function combineCustomerAllowedDeliveryMethods($erpData, $erpCustomerGroup)
    {

        $parent = $erpData->getDeliveryMethodCodes();
        $null = new \Zend_Db_Expr("NULL");
        $emptyArr = array();
        $mappedCodes = array();

        if ($parent) {
            //$helper = Mage::helper('customerconnect');
            $model = $this->commErpMappingShippingmethodFactory->create();
            $att = $parent->getData('_attributes');
            $deliveryMethodCodes = $this->_uploadModel->_getGroupedData('deliveryMethodCodes', 'deliveryMethodCode', $erpData);
            if (is_object($att)) {
                $exclude = $att->getExclude();
            }
            foreach ($deliveryMethodCodes as $key => $code) {
                $mappedValues = $model->loadAllMappingByStore($code, 'erp_code', 'shipping_method_code');
                if (!empty($mappedValues)) {
                    unset($deliveryMethodCodes[$key]);
                    $mappedCodes = array_diff($mappedCodes, array($code));
                    $mappedCodes = array_merge($deliveryMethodCodes, $mappedValues, $mappedCodes);
                }
            }
            if ($exclude == 'N') {
                if (array_filter($mappedCodes)) {
                    $erpCustomerGroup->setAllowedDeliveryMethods(serialize($mappedCodes));
                    $erpCustomerGroup->setAllowedDeliveryMethodsExclude($null);
                } else {
                    $erpCustomerGroup->setAllowedDeliveryMethods(serialize($emptyArr));
                    $erpCustomerGroup->setAllowedDeliveryMethodsExclude($null);
                }
            } else {
                if (array_filter($mappedCodes)) {
                    $erpCustomerGroup->setAllowedDeliveryMethodsExclude(serialize($mappedCodes));
                    $erpCustomerGroup->setAllowedDeliveryMethods($null);
                } else {
                    $erpCustomerGroup->setAllowedDeliveryMethods($null);
                    $erpCustomerGroup->setAllowedDeliveryMethodsExclude($null);
                }
            }
        }
    }

    private function combineCustomerAllowedPaymentMethods($erpData, $erpCustomerGroup)
    {

        $parent = $erpData->getPaymentMethodCodes();
        $null = new \Zend_Db_Expr("NULL");
        $emptyArr = array();
        $mappedCodes = array();
        if ($parent) {
            $model = $this->commErpMappingPaymentFactory->create();
            $att = $parent->getData('_attributes');
            $paymentMethodCodes = $this->_uploadModel->_getGroupedData('paymentMethodCodes', 'paymentMethodCode', $erpData);
            if (is_object($att)) {
                $exclude = $att->getExclude();
            }
            foreach ($paymentMethodCodes as $key => $code) {

                $mappedValues = $model->loadAllMappingByStore($code, 'erp_code', 'magento_code');
                if (!empty($mappedValues)) {
                    unset($paymentMethodCodes[$key]);
                    $mappedCodes = array_diff($mappedCodes, array($code));
                    $mappedCodes = array_merge($paymentMethodCodes, $mappedValues, $mappedCodes);
                }
                // $mappedValue = $mappedObj->getMagentoCode();
                //$paymentMethodCodes[$key] = $mappedValue;
            }
            if ($exclude == 'N') {
                if (array_filter($mappedCodes)) {
                    $erpCustomerGroup->setAllowedPaymentMethods(serialize($mappedCodes));
                    $erpCustomerGroup->setAllowedPaymentMethodsExclude($null);
                } else {
                    $erpCustomerGroup->setAllowedPaymentMethods(serialize($emptyArr));
                    $erpCustomerGroup->setAllowedPaymentMethodsExclude($null);
                }
            } else {
                if (array_filter($mappedCodes)) {
                    $erpCustomerGroup->setAllowedPaymentMethodsExclude(serialize($mappedCodes));
                    $erpCustomerGroup->setAllowedPaymentMethods($null);
                } else {

                    $erpCustomerGroup->setAllowedPaymentMethods($null);
                    $erpCustomerGroup->setAllowedPaymentMethodsExclude($null);
                }
            }
        }
    }

    /**
     * Processes centralCollection tag
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpCustomerGroup
     */
    private function combineCustomerCentralCollection($erpData, $erpCustomerGroup)
    {
        $isCentralCollection = ($erpData->getCentralCollection() && $erpData->getCentralCollection() === 'Y') ? 1 : 0;
        $this->setFieldValue('is_central_collection', $isCentralCollection, 'central_collection', $erpCustomerGroup);
    }


    /**
     * Return If Overriding of Shopper addressees is Yes/No
     *
     * @return boolean
     */
    public function shopperDefaultAddresses()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SHOPPER_DEFAULT_ADDRESSES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

    }//end shopperDefaultAddresses()


    /**
     * Allow Overriding of default billing address
     *
     * @param mixed $customer Customer model.
     *
     * @return boolean
     */
    public function shopperBillingDefault($customer)
    {
        return (!$customer->getDefaultBilling()) ? true : $this->shopperDefaultAddresses();

    }//end shopperBillingDefault()


    /**
     * Allow Overriding of default shipping address
     *
     * @param mixed $customer Customer model.
     *
     * @return boolean
     */
    public function shopperShippingDefault($customer)
    {
        return (!$customer->getDefaultShipping()) ? true : $this->shopperDefaultAddresses();

    }//end shopperShippingDefault()


}