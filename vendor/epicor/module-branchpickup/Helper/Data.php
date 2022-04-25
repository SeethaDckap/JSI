<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Helper;


/**
 * Branch Helper
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{


    const FRONTEND_RESOURCE = 'Epicor_Checkout::checkout_branch_pickup';

    private $_locations;
    protected $_locationHelper;
    protected $_locationConfig = array("B2B", "B2C");
    private $_selected = array();

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /*
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;

    /**
     * @var \Epicor\BranchPickup\Model\BranchpickupFactory
     */
    protected $branchPickupBranchpickupFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerCustomer;

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Lists\Helper\Session $listsSessionHelper,
        \Epicor\BranchPickup\Model\BranchpickupFactory $branchPickupBranchpickupFactory,
        \Magento\Customer\Model\Customer $customerCustomer,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper,
        \Epicor\AccessRight\Helper\Data $authorization
    ) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->storeManager = $storeManager;
        $this->listsSessionHelper = $listsSessionHelper;
        $this->branchPickupBranchpickupFactory = $branchPickupBranchpickupFactory;
        $this->customerCustomer = $customerCustomer;
        $this->commLocationFactory = $commLocationFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        $this->_accessauthorization = $authorization->getAccessAuthorization();
        parent::__construct(
            $context
        );
    }


    /**
     * Checks  whether the branch pickup is available for the shopper
     * @return boolean
     */
    public function isBranchPickupAvailable()
    {
        if(!$this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE)){
            return false;
        }
        /* if contract is enabled branch pickup is not allowed */
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $contractEnabled = $contractHelper->contractsEnabled();
        
        $locationEnabled = $this->isLocationEnabled();
        
        if ($contractEnabled || !$locationEnabled) {
            return false;
        }
        
        /* Check if Delivery method is allowed for ERP Account */
        if (!$this->checkErpAllowed()) {
            return false;
        }

        /* @var $helper Epicor_BranchPickup_Helper_Data */
        //Checks  whether the branch pickup is enabled for a guest (non logged user or not)
        $checkGuestUser = $this->checkGuestUser();
        //Check SalesRep Access
        $checkSalesRep = $this->checkSalesRep();
        //Check Supplier Access
        $checkSupplier = $this->checkSupplier();
        if ((!$checkSalesRep) || (!$checkSupplier)) {
            return false;
        }

        $getSelected = $this->getCustomerAllowedLocations();
        if (($checkGuestUser) && (count($getSelected) > 0)) {
            return true;
        }

        $branchpickupEnabled = $this->branchPickupEnabled();
        if (($branchpickupEnabled) && (count($getSelected) > 0)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function isLocationEnabled()
    {
        return $this->scopeConfig->isSetFlag('epicor_comm_locations/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }    

    /**
     * Checks  whether the branch pickup is enabled for a guest (non logged user or not)
     * @return boolean
     */
    public function checkGuestUser()
    {
        //M1 > M2 Translation Begin (Rule 58)
        //$isLoggedIn = Mage::helper('customer')->isLoggedIn();
        $isLoggedIn = $this->customerSession->isLoggedIn();
        //M1 > M2 Translation End
        $allowed = false;
        if (!$isLoggedIn) {
            $checkVals = $this->checkGlobalBranchPickupAllowed();
            //1 => yes , 2=>'B2C'
            //1 => yes , 2=>'B2C'
            if (in_array($checkVals, array("1", "2")) && ($this->branchPickupActive())) {
                $allowed = true;
            } else {
                $allowed = false;
            }
        }
        return $allowed;
    }

    /**
     * Checks  whether the branch pickup is enabled for a shopper or not
     * @return boolean
     */
    public function branchPickupEnabled()
    {

        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $checkShopperType = $this->checkCustomerShopperType();
        //Shopper associated with the default account
        $checkDefaultAccount = $this->checkNonErpSettings();
        //IF the Site level settings of Location is B2B/B2C and if it was not matching the shopper type
        if (!$checkShopperType || !$checkDefaultAccount) {
            return false;
        }

        $checkCustomer = $customer->getEccIsBranchPickupAllowed();
        $getAccountType = $customer->getEccErpAccountType();
        //check non ERP customer
        $checkGuest = $this->checkNonErpCustomer();
        if ($getAccountType == "guest") {
            return $this->isAllowedNonErpcustomer($checkGuest);
        } else {
            return $this->isAllowedErpcustomer();
        }
    }

    public function checkNonErpSettings()
    {
        $checkGlobalType = $this->checkShopperTypeString();
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $erpAccountId = $customer->getEccErpaccountId();
        $allowedConfig = array("B2B", "B2C", "Y", "N");
        $defaultErpAccountId = $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        $branchPickupAllowed = $customer->getEccIsBranchPickupAllowed();
        //1 - yes
        //2 - global default
        //0 - no
        $available = true;
        if (!$erpAccountId) {
            if (($checkGlobalType == "Y") && (in_array($branchPickupAllowed, array('1', '2')))) {
                $available = true;
            } elseif (($checkGlobalType == "N") && (in_array($branchPickupAllowed, array('1')))) {
                $available = true;
            } elseif (($checkGlobalType == "B2B") && (in_array($branchPickupAllowed, array('1')))) {
                $available = true;
            } elseif (($checkGlobalType == "B2C") && (in_array($branchPickupAllowed, array('1', '2')))) {
                $available = true;
            } else {
                $available = false;
            }
        }
        return $available;
    }

    public function checkShopperTypeString()
    {
        $checkCustomerLocationType = $this->checkGlobalBranchPickupAllowed();
        switch ($checkCustomerLocationType) {
            case "2":
                $type = "B2C";
                break;
            case "3":
                $type = "B2B";
                break;
            case "1":
                $type = "Y";
                break;
            case "0":
                $type = "N";
                break;
            default:
                $type = $checkCustomerLocationType;
        }
        return $type;
    }

    /**
     * Check branch pickup is allowed or not for guest(non Erp Customer)
     *
     * 
     *
     * @return boolean
     */
    public function isAllowedNonErpcustomer($checkGuest)
    {
        if (strcmp($checkGuest, 'disablebranchpickup') == 0) {
            return false;
        }
        if (($checkGuest) && ($this->branchPickupActive())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check branch pickup is allowed or not for  Erp Customer
     *
     * 
     *
     * @return boolean
     */
    public function isAllowedErpcustomer()
    {
        //check branch pickup allowed for the customer
        $checkCustomer = $this->checkCustomerBranchPickupAllowed();
        //check ERP level branchpickup allowed for the customer
        $checkErp = $this->checkErpBranchPickupAllowed();
        // if checkCustomer == disablebranchpickup(ie. branch pickup was disabled in customer level)
        if (strcmp($checkCustomer, 'disablebranchpickup') == 0) {
            return false;
        }

        if (($checkCustomer || $checkErp) && ($this->branchPickupActive())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * IF the Site level settings of Location is B2B/B2C and if it was not matching the shopper type
     *
     * 
     *
     * @return boolean
     */
    public function checkCustomerShopperType()
    {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $erpAccountId = $customer->getEccErpaccountId();
        $checkCustomer = $customer->getEccIsBranchPickupAllowed();
        $isAllowed = true;
        if ($erpAccountId) {
            $commHelper = $this->commHelper;
            /* @var $helper Epicor_Comm_Helper_Data */
            $erpAccount = $commHelper->getErpAccountInfo();
            $erpAccountbranchPickup = $erpAccount->getIsBranchPickupAllowed();
            ;
            $LocationGlobalConfig = $this->checkShopperType();
            if (in_array($LocationGlobalConfig, $this->_locationConfig)) {
                //if the shopper type is 'Erp Global Default' && 'ERP Default is 1(yes)'
                if (($checkCustomer == 2) && ($erpAccountbranchPickup == 1)) {
                    return true;
                } else {

                    if (($erpAccount->getAccountType() == $LocationGlobalConfig) || ($checkCustomer == 1)) {
                        $isAllowed = true;
                    } else {
                        $isAllowed = false;
                    }
                }
            }
        }
        return $isAllowed;
    }

    public function checkShopperType()
    {
        $checkCustomerLocationType = $this->checkGlobalBranchPickupAllowed();
        switch ($checkCustomerLocationType) {
            case "2":
                $type = "B2C";
                break;
            case "3":
                $type = "B2B";
                break;
            default:
                $type = $checkCustomerLocationType;
        }
        return $type;
    }

    /**
     * Checks  branch pickup for non erp customers (i.e guest)
     *
     * 
     *
     * @return globalconfiguration
     */
    public function checkNonErpCustomer()
    {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $checkCustomer = $customer->getEccIsBranchPickupAllowed();
        $getAccountType = $customer->getEccErpAccountType();
        if ($getAccountType == "guest") {
            if ($checkCustomer == "2") {
                $isGuestBranchPickupAllowed = $this->checkGlobalBranchPickupAllowed();
                $checkGlobal = ($isGuestBranchPickupAllowed) ? 1 : "disablebranchpickup";
            } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
                $checkGlobal = 'disablebranchpickup';
            } else {
                $checkGlobal = $checkCustomer;
            }
            return $checkGlobal;
        }
    }

    /**
     * Checks  whether the branch pickup is active or not in shipping method
     * @return boolean
     */
    public function branchPickupActive()
    {
        return $this->scopeConfig->getValue("carriers/eccbranchpickup/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Location Helper
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getLocationHelper()
    {
        if (!$this->_locationHelper) {
            $this->_locationHelper = $this->commLocationsHelper;
            /* @var $helper Epicor_Comm_Helper_Locations */
        }
        return $this->_locationHelper;
    }

    /**
     * Checks  customer attribute branch pickup is enabled or not
     * if it returns "disablebranchpickup", then in customer account
     * information Branch pickup was disabled
     * @return boolean
     */
    public function checkCustomerBranchPickupAllowed()
    {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $checkCustomer = $customer->getEccIsBranchPickupAllowed();
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpBranchPickupAllowed();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disablebranchpickup';
        } else {
            $checkGlobal = $checkCustomer;
        }

        return $checkGlobal;
    }

    /**
     * Checks  ERP level branch pickup is enabled or not
     *
     * 
     *
     * @return boolean
     */
    public function checkErpBranchPickupAllowed()
    {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getIsBranchPickupAllowed();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalBranchPickupAllowed();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global Branch Pickup Allowed or Not
     * @return boolean
     */
    public function checkGlobalBranchPickupAllowed()
    {


        $storeBranchPickup = $this->scopeConfig->getValue('epicor_comm_locations/global/isbranchpickupallowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $storeBranchPickup;
    }

    /**
     * Get the selected branch details from session
     * @return selected location code
     */
    public function getSelectedBranch()
    {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        return $sessionHelper->getValue('ecc_selected_branchpickup');
    }

    /**
     * Checks if the given id is a valid branch
     * @param integer $locationCode
     * @return boolean
     */
    public function isValidLocation($locationCode)
    {
        $currentLocations = $this->getCustomerAllowedLocations();
        if (in_array($locationCode, array_keys($currentLocations))) {
            return $locationCode;
        }
    }

    /**
     * Reset the location filter 
     * 
     * 
     */
    public function resetBranchLocationFilter()
    {
        $getLocations = $this->getCustomerAllowedLocations();
        if (!empty($getLocations)) {
            $helper = $this->commLocationsHelper;
            /* @var $helper Epicor_Comm_Helper_Locations */
            $helper->setCustomerDisplayLocationCodes(array_keys($getLocations));
        }
    }

    /**
     * Sets the given id as the selected BranchPickup
     * @param integer $locationCode
     */
    public function selectBranchPickup($locationCode,$noBsv=false, $emptyCartCheck = false)
    {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        /* @var Epicor_BranchPickup_Model_Branchpickup */
        $sessionHelper->setValue('ecc_selected_branchpickup', $locationCode);
        
        $this->branchPickupBranchpickupFactory->create()->updateBranchLocationsQuote($locationCode,$noBsv,$emptyCartCheck);
    }

    /**
     * Empty the selected BranchPickup
     * @param integer $locationCode
     */
    public function emptyBranchPickup()
    {

        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper \Epicor\Lists\Helper\Session */
        $sessionHelper->setValue('ecc_selected_branchpickup', '');
    }

    public function getSelected()
    {
        $allowed = $this->getCustomerAllowedLocations();
        foreach ($allowed as $locationCode) {
            $locationValue = $locationCode->getCode();
            $this->_selected[$locationValue] = array(
                'code' => $locationValue
            );
        }
        return $this->_selected;
    }

    public function getSelectedOptions()
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        if ($helper->isMasquerading()) {
            $erpAccount = $helper->getErpAccountInfo(null, 'customer');
        } else {
            $erpAccount = $this->getCustomerErpAccount();
        }
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $allowed = $erpAccount->getAllowedLocationCodes();
        foreach ($allowed as $locationCode) {
            $this->_selected[$locationCode] = array(
                'code' => $locationCode
            );
        }
        return $this->_selected;
    }

    public function getCustomerLocations()
    {
        $helper = $this->commLocationsHelper;
        /* @var $helper Epicor_Comm_Helper_Locations */
        $allowed = $helper->getCustomerDisplayLocationCodes();

        return $allowed;
    }

    /**
     * Get session customer allowed locations
     * 
     * @return array
     */
    public function getCustomerAllowedLocations()
    {
        $locations = $this->getLocationHelper()->getCustomerAllowedLocations();

        if (!is_array($locations)) {
            $locations = array();
        }
        return $locations;
    }

    /**
     * get Customer Erp Account
     * 
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getCustomerErpAccount()
    {
        return $this->_getErpAccount();
    }

    /**
     * get Customer Erp Account for type
     * 
     * @param string $type
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    private function _getErpAccount($type = 'customer')
    {

        $customerId = $this->customerSession->getCustomer()->getId();
        $customer = $this->customerCustomer->load($customerId);
        if (empty($this->_erpAccount)) {

            $helper = $this->commHelper;
            /* @var $helper Epicor_Comm_Helper_Data */

            if ($type == 'customer') {
                $erpAccountId = $customer->getEccErpaccountId();
            } else {
                $erpAccountId = $customer->getEccSupplierErpaccountId();
            }

            $this->_erpAccount = $helper->getErpAccountInfo($erpAccountId, $type);
        }

        return $this->_erpAccount;
    }

    /**
     * OrderFor Data
     * 
     * @param string $locationcode
     * @return address Data
     */
    public function getOrderFor($locationCode, $carriageText = null, $isGorname = null)
    {
        $location = $this->commLocationFactory->create();
        /* @var $location Epicor_Comm_Model_Location */
        $getLocationData = $location->load($locationCode, 'code');

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $customer = $this->customerSession->getCustomer();
        //M1 > M2 Translation Begin (Rule 58)
        //$isLoggedIn = Mage::helper('customer')->isLoggedIn();
        $isLoggedIn = $this->customerSession->isLoggedIn();
        //M1 > M2 Translation End

        //For Guest 
        if ((!$isLoggedIn) && ($isGorname)) {
            $Name = explode(' ', $isGorname, 2);
            $firstName = $Name[0];
            $lastName = !empty($Name[1]) ? $Name[1] : ",";
            $fullName = $firstName . " " . $lastName;
        } else {
            $Name = explode(' ', $customer->getName(), 2);
            $firstName = $Name[0];
            $lastName = !empty($Name[1]) ? $Name[1] : ",";
            $fullName = $firstName . " " . $lastName;
        }

        $countyCode = $getLocationData->getCountyCode();
        $regionId = '';
        if (!empty($countyCode)) {
            $countryModel = $this->directoryCountryFactory->create()->loadByCode($getLocationData->getCountry());
            $countyCode = $this->directoryRegionFactory->create()->loadByCode($getLocationData->getCountyCode(), $countryModel->getId());
            $regionId = $countyCode->getRegionId();
        }

        if (!empty($getLocationData)) {
            $addressData = array(
                'contactName' => $helper->stripNonPrintableChars($fullName),
                'addressCode' => $this->getDefaultErpAddressCode(),
                'name' => $helper->stripNonPrintableChars($getLocationData->getName()),
                //M1 > M2 Translation Begin (Rule 9)
                /*'address1' => $helper->stripNonPrintableChars($getLocationData->getAddress1()),
                'address2' => $helper->stripNonPrintableChars($getLocationData->getAddress2()),
                'address3' => $helper->stripNonPrintableChars($getLocationData->getAddress3()),*/
                'address1' => $helper->stripNonPrintableChars($getLocationData->getData('address1')),
                'address2' => $helper->stripNonPrintableChars($getLocationData->getData('address2')),
                'address3' => $helper->stripNonPrintableChars($getLocationData->getData('address3')),
                //M1 > M2 Translation End
                'city' => $helper->stripNonPrintableChars($getLocationData->getCity()),
                'county' => $helper->stripNonPrintableChars($getLocationData->getCounty()),
                'country' => $helper->getErpCountryCode($getLocationData->getCountry()),
                'postcode' => $helper->stripNonPrintableChars($getLocationData->getPostcode()),
                'emailAddress' => $helper->stripNonPrintableChars($getLocationData->getEmailAddress()),
                'telephoneNumber' => $helper->stripNonPrintableChars($getLocationData->getTelephoneNumber()),
                'mobileNumber' => $helper->stripNonPrintableChars($getLocationData->getMobileNumber()),
                'faxNumber' => $helper->stripNonPrintableChars($getLocationData->getFaxNumber())
            );
            if ($carriageText) {
                $addressData['carriageText'] = '';
            }
        }

        return $addressData;
    }

    private function getDefaultErpAddressCode()
    {
        return $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code');
    }

    /**
     * get pickup address 
     * 
     * @param string $locationcode
     * @return address Data
     */
    public function getPickupAddress($locationCode,$magentoCode=false)
    {
        $location = $this->commLocationFactory->create();
        /* @var $location Epicor_Comm_Model_Location */
        $getLocationData = $location->load($locationCode, 'code');

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $customer = $this->customerSession->getCustomer();

        if (!empty($getLocationData)) {
            $Name = explode(' ', $customer->getName(), 2);
            $firstName = $Name[0];
            $lastName = !empty($Name[1]) ? $Name[1] : "";
            //M1 > M2 Translation Begin (Rule 9)
            /*$addressD1 = $getLocationData->getAddress1();
            $addressD2 = $getLocationData->getAddress2();
            $addressD3 = $getLocationData->getAddress3();*/
            $addressD1 = $getLocationData->getData('address1');
            $addressD2 = $getLocationData->getData('address2');
            $addressD3 = $getLocationData->getData('address3');
            //M1 > M2 Translation End
            $address1 = !empty($addressD1) ? $helper->stripNonPrintableChars($addressD1) : "";
            $address2 = !empty($addressD2) ? "" . $helper->stripNonPrintableChars($addressD2) : "";
            $address3 = !empty($addressD3) ? "" . $helper->stripNonPrintableChars($addressD3) : "";
            $address = array($address1,$address2,$address3);
            $emptystreet = false;
            if(!$address1 && !$address2 && !$address3) {
                $emptystreet= true;
            }
            $countyCode = $getLocationData->getCountyCode();
            $regionId = '';
            if (!empty($countyCode)) {
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($getLocationData->getCountry());
                $countyCode = $this->directoryRegionFactory->create()->loadByCode($getLocationData->getCountyCode(), $countryModel->getId());
                $regionId = $countyCode->getRegionId();
            }
            $error = 0;
            if($emptystreet || !$getLocationData->getCity() || !$getLocationData->getPostcode() || !$getLocationData->getCountry() || !$getLocationData->getTelephoneNumber()) {
               $error = 1; 
            }
            
            if($magentoCode) {
                $formatCountry = $getLocationData->getCountry();
            } else {
                $formatCountry = $helper->getErpCountryCode($getLocationData->getCountry());
            }
            
            
            if(!empty($getLocationData->getCountry())) {
                $stateArray = $this->directoryCountryFactory->create()->setId($getLocationData->getCountry())->getLoadedRegionCollection()->toOptionArray(); 
                if((!empty($stateArray)) && (!($getLocationData->getCountyCode()))) {
                   $error = 1; 
                }
            }      
            
            
            
            $addressData = array(
                'locationid' => $helper->stripNonPrintableChars($getLocationData->getId()),
                'locationname' => $getLocationData->getName(),
                'firstname' => $helper->stripNonPrintableChars($firstName),
                'lastname' => $helper->stripNonPrintableChars($lastName),
                'street1' => trim($address1),
                'street2' => trim($address2),
                'code' => $locationCode,
                'street3' => trim($address3),
                'city' => $helper->stripNonPrintableChars($getLocationData->getCity()),
                'region' => $helper->stripNonPrintableChars($getLocationData->getCounty()),
                'region_id' => $regionId,
                'country_id' => $formatCountry,
                'postcode' => $helper->stripNonPrintableChars($getLocationData->getPostcode()),
                'email' => $helper->stripNonPrintableChars($getLocationData->getEmailAddress()),
                'telephone' => $helper->stripNonPrintableChars($getLocationData->getTelephoneNumber()),
                'mobile_number' => $helper->stripNonPrintableChars($getLocationData->getMobileNumber()),
                'fax' => $helper->stripNonPrintableChars($getLocationData->getFaxNumber()),
                'error' => $error,
                'street' => $address
            );

            if (empty($firstName)) {
                unset($addressData['firstname']);
                unset($addressData['lastname']);
            }
        }

        return $addressData;
    }

    /**
     * get pickup address for OrderBy
     * 
     * @param int customerid.
     * @return address Data
     */
    public function getOrderBy()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $customerBranch = $this->customerCustomerFactory->create()->load($customerId);

        if (!empty($customerBranch)) {
            $customerBranchDetails = array(
                'contactCode' => $customerBranch->getContactCode(),
                'name' => $customerBranch->getName(),
                'function' => $customerBranch->getFunction(),
                'telephoneNumber' => $customerBranch->getTelephone(),
                'mobileNumber' => $customerBranch->getMobileNumber(),
                'faxNumber' => $customerBranch->getFaxNumber(),
                'emailAddress' => $customerBranch->getEmail(),
                'eccLoginId' => $customerBranch->getId()
            );
        }

        return $customerBranchDetails;
    }

    /**
     * check customer Account Type
     *
     * @return salesrep
     */
    public function checkCustomerAccountType()
    {
        $customerSession = $this->customerSession;
        /* @var $customer Epicor_Comm_Model_Customer */
        $customer = $customerSession->getCustomer();
        $customerVals['type'] = $customer->getEccErpAccountType();
        $customerVals['erpId'] = $customerSession->getMasqueradeAccountId();

        return $customerVals;
    }

    /**
     * check salresrep Account
     *
     * @return salesrep
     */
    public function salesRepRedirect()
    {
        $checkCustomerType = $this->checkCustomerAccountType();
        $helperBranchPickup = $this->branchPickupBranchpickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Branchpickup */
        if (($checkCustomerType['type'] == "salesrep") && ($checkCustomerType['erpId'] == "")) {
            //M1 > M2 Translation Begin (Rule p2-4)
            //$redirectUrl = Mage::getUrl('salesrep/account/index', $helperBranchPickup->issecure());
            $redirectUrl = $this->_getUrl('salesrep/account/index', $helperBranchPickup->issecure());
            //M1 > M2 Translation End
            return $redirectUrl;
        }
    }

    /**
     * check salresrep Access
     *
     * @return salesrep
     */
    public function checkSalesRep()
    {
        $checkCustomerType = $this->checkCustomerAccountType();
        $helperBranchPickup = $this->branchPickupBranchpickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Branchpickup */
        if (($checkCustomerType['type'] == "salesrep") && ($checkCustomerType['erpId'] == "")) {
            //$redirectUrl = Mage::getUrl('salesrep/account/index', $helperBranchPickup->issecure());
            //return $redirectUrl;
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    /**
     * check supplier Access
     *
     * @return salesrep
     */
    public function checkSupplier()
    {
        $checkCustomerType = $this->checkCustomerAccountType();
        if ($checkCustomerType['type'] == "supplier") {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }


    /**
     * IF Delivery Method Branch Pickup needs to be excluded/included at ERP Account level.
     *
     * 
     *
     * @return boolean
     */
    public function checkErpAllowed()
    {

        $validShippingMethods = array();
        $invalidShippingMethods = array();
        $branchPickupCode = \Epicor\BranchPickup\Model\Carrier\Epicorbranchpickup::ECC_BRANCHPICKUP_COMBINE;
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $erpAccountId = $customer->getEccErpaccountId();
        if (!$erpAccountId) {
            $erpAccountId = $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        $isAllowed = true;
        if ($erpAccountId) {
            $commHelper = $this->commHelper;
            /* @var $helper Epicor_Comm_Helper_Data */
            $erpAccount = $commHelper->getErpAccountInfo();
            $getAccountType = $erpAccount->getAccountType();
            $allowedTypes = array("B2B", "B2C");
            if (in_array($getAccountType, $allowedTypes)) {
                if (!(is_null($erpAccount->getAllowedDeliveryMethods()) &&
                    is_null($erpAccount->getAllowedDeliveryMethodsExclude()))) {

                    $exclude = !is_null($erpAccount->getAllowedDeliveryMethods()) ? 'N' : 'Y';
                    $validShippingMethods = unserialize($erpAccount->getAllowedDeliveryMethods());
                    $invalidShippingMethods = unserialize($erpAccount->getAllowedDeliveryMethodsExclude());
                    if ($exclude == 'N') {
                        if (!in_array($branchPickupCode, $validShippingMethods)) {
                            $isAllowed = false;
                        }
                    } else {
                        if (in_array($branchPickupCode, $invalidShippingMethods)) {
                            $isAllowed = false;
                        }
                    }
                }
            }
        }
        return $isAllowed;
    }
    
    /**
     *  Redirect to Branch Pickup is Yes/No
     * 
     * @return boolean
     */
    public function redirectToBranchpickup()
    {
        return $this->scopeConfig->getValue('epicor_comm_locations/global/redirecttobranchpickup', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Check if Selected Location is shown
     * 
     * @return boolean
     */
    public function showSelectedLocation()
    {
        return $this->scopeConfig->isSetFlag('epicor_comm_locations/global/showselectedlocation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
