<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Helper;

/**
 * Dealers Helper
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper 
{
   
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /*
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerCustomer;
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;
    
    
    protected $customerRepository;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    
    protected $urlDecoder;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $dealerGroupModelFactory;

    /**
     * @var \Epicor\Common\Model\ResourceModel\DataMapping\CollectionFactory
     */
    protected $dataMappingCollectionFactory;

    protected $fetchDealerGroup;

    protected $_dealerControllers = [
        'quotes',
        'orders'
    ];
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customerCustomer,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Dealerconnect\Model\DealergroupsFactory $dealerGroupModelFactory,
        \Magento\Framework\Url\Decoder $urlDecoder,
        \Epicor\Common\Model\ResourceModel\DataMapping\CollectionFactory $dataMappingCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
        $this->customerCustomer = $customerCustomer;
        $this->request = $request;
        $this->eavConfig = $eavConfig;
        $this->customerRepository = $customerRepository;
        $this->encryptor = $encryptor;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->urlDecoder = $urlDecoder;
        $this->dealerGroupModelFactory = $dealerGroupModelFactory;
        $this->dataMappingCollectionFactory = $dataMappingCollectionFactory;

        parent::__construct(
            $context
        );
    }
    
    
     /**
     * Checks  customer attribute branch pickup is enabled or not
     * if it returns "disabletoggle", then in customer account
     * information Is toggle value is disabled
     * @return boolean
     */
    public function checkCustomerToggleAllowed()
    {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $checkCustomer = $customer->getEccIsToggleAllowed();
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpToggleAllowed();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disabletoggle';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if($checkGlobal == "0"){
            $checkGlobal = 'disabletoggle';
        }
        return $checkGlobal;
    }
    
     /**
     * Checks  ERP level Toggle is enabled or not
     * @return boolean
     */
    public function checkErpToggleAllowed()
    {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getIsToggleAllowed();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalToggleAllowed();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global Toggle Allowed or Not
     * @return boolean
     */
    public function checkGlobalToggleAllowed()
    {
        $storeToggleallowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/dealer_settings/is_toggle_allowed',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $storeToggleallowed;
    }
    
    
     /**
     * Checks  customer attribute Login Mode Type Setting values
     * if it returns "dealer", then login Mode "End Customer"
      * if it returns "shopper", then login Mode "Dealer"
     * @return string
     */
    public function checkCustomerLoginModeType()
    {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $checkCustomer = $customer->getEccLoginModeType();
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpLoginModeType();
        } else if (($checkCustomer == null) || (empty($checkCustomer))) {
            $checkGlobal = 'shopper';
        } else {
            $checkGlobal = $checkCustomer;
        }

        return $checkGlobal;
    }
    
     /**
     * Checks  ERP level Login mode type setting values (2 , shopper , dealer)
     * if it returns "dealer", then login Mode "End Customer"
      * if it returns "shopper", then login Mode "Dealer"
     * @return string 
     */
    public function checkErpLoginModeType()
    {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getLoginModeType();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalLoginModeType();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }
    
     /**
     * Checks  Global Login mode type values (shopper , dealer)
     * if it returns "dealer", then login Mode "End Customer"
     * if it returns "shopper", then login Mode "Dealer"
     * @return string
     */
    public function checkGlobalLoginModeType()
    {
        $storeLoginModeType = $this->scopeConfig->getValue('dealerconnect_enabled_messages/dealer_settings/login_mode_type',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $storeLoginModeType;
    }
    
     /**
     * Checks  customer attribute branch pickup is enabled or not
     * if it returns "disabletoggle", then in customer account
     * information Is toggle value is disabled
     * @return boolean
     */
    public function checkCustomerCusPriceAllowed() {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        if($this->checkCustomerToggleAllowed() == "disabletoggle"){
            return 'disable';
        }
        $checkCustomer = $customer->getEccShowCustomerPrice();
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpCusPriceAllowed();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disable';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = 'disable';
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level Toggle is enabled or not
     * @return boolean
     */
    public function checkErpCusPriceAllowed() {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getShowCustomerPrice();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalCusPriceAllowed();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global Toggle Allowed or Not
     * @return boolean
     */
    public function checkGlobalCusPriceAllowed() {
        $cusPriceAllowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/dealer_settings/show_customer_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $cusPriceAllowed;
    }

    /**
     * Checks  customer attribute branch pickup is enabled or not
     * if it returns "disabletoggle", then in customer account
     * information Is toggle value is disabled
     * @return boolean
     */
    public function checkCustomerMarginAllowed() {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        if ($this->checkCustomerToggleAllowed() == "disabletoggle") {
            return 'disable';
        }
        $checkCustomer = $customer->getEccShowMargin();
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpMarginAllowed();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disable';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = 'disable';
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level Toggle is enabled or not
     * @return boolean
     */
    public function checkErpMarginAllowed() {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getShowMargin();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalMarginAllowed();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global Toggle Allowed or Not
     * @return boolean
     */
    public function checkGlobalMarginAllowed() {
        $marginAllowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/dealer_settings/show_margin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $marginAllowed;
    }

    /**
     * Checks if customer in session is type dealer
     * @return boolean
     */
    public function dealerLoggedIn()
    {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        return $customer->isDealer();
    }
    
    /**
     * Checks if page is DealerConnect quotes page
     * @return boolean
     */
    public function isDealerQuotePortal()
    {
         $controller = $this->request->getControllerName();
         $module = $this->request->getModuleName();
         $action = $this->request->getActionName();
         if ($module === 'dealerconnect' && $controller === 'quotes' && ($action !== "index")) {
             return true;
         }else {
             return false;
         }
    }
    
    /**
     * Checks if page is DealerConnect quotes/order page
     * @return boolean
     */
    public function isDealerPortal()
    {
         $controller = $this->request->getControllerName();
         $module = $this->request->getModuleName();
         $action = $this->request->getActionName();
         if ($module === 'dealerconnect' && in_array($controller, $this->_dealerControllers)) {
             return true;
         }else {
             return false;
         }
    }
    
     /*
     * DEID parse reponse from XML data
     */
    
    public function DeidParseResponse($response=null){
       $deid_data = array(); 
       if($response!=null){
            if($response->getLocationNumber()!=null){
                 $deid_data = array(
                     'locationNumber'=>$response->getLocationNumber(),
                     'identificationNumber'=>($response->getIdentificationNumber()) ?$response->getIdentificationNumber() : "",
                     'serialNumber'=>($response->getSerialNumber()) ? $response->getSerialNumber() : null,
                     'productCode'=>($response->getProductCode()) ? $response->getProductCode() :null,
                     'description'=>($response->getDescription()) ? $response->getDescription() : null,
                     'orderNumber'=>($response->getOrderNumber()) ? $response->getOrderNumber() : null
                 );
                 
                 $obj_ownder_add = $response->getOwnerAddress();
                 
                 if(!empty($obj_ownder_add) && $obj_ownder_add!=null){
                        $owner_address_data= array(
                            'accountNumber' =>$obj_ownder_add->getAccountNumber(),
                            'addressCode' =>$obj_ownder_add->getAddressCode(),
                            'name' =>$obj_ownder_add->getName(),
                            'contactName' =>$obj_ownder_add->getContactName(),
                            'address1' =>$obj_ownder_add->getAddress1(),
                            'address2' =>$obj_ownder_add->getAddress2(),
                            'address3' =>$obj_ownder_add->getAddress3(),
                            'city' =>$obj_ownder_add->getCity(),
                            'county' =>$obj_ownder_add->getCounty(),
                            'country' =>$obj_ownder_add->getCountry(),
                            'postcode' =>$obj_ownder_add->getPostcode(),
                            'telephoneNumber' =>$obj_ownder_add->getTelephoneNumber(),
                            'faxNumber' =>$obj_ownder_add->getFaxNumber(),
                            'emailAddress' =>$obj_ownder_add->getEmailAddress()

                        );
                      $deid_data['ownerAddress']= $owner_address_data;
                 }
                 
                  $soldToAddress = $response->getSoldToAddress();
                   
                  if(!empty($soldToAddress) && $soldToAddress!=null){
	$sold_to_address_data= array(
                            'accountNumber' =>$soldToAddress->getAccountNumber(),
                            'addressCode' =>$soldToAddress->getAddressCode(),
                            'name' =>$soldToAddress->getName(),
                            'contactName' =>$soldToAddress->getContactName(),
                            'address1' =>$soldToAddress->getAddress1(),
                            'address2' =>$soldToAddress->getAddress2(),
                            'address3' =>$soldToAddress->getAddress3(),
                            'city' =>$soldToAddress->getCity(),
                            'county' =>$soldToAddress->getCounty(),
                            'country' =>$soldToAddress->getCountry(),
                            'postcode' =>$soldToAddress->getPostcode(),
                            'telephoneNumber' =>$soldToAddress->getTelephoneNumber(),
                            'faxNumber' =>$soldToAddress->getFaxNumber(),
                            'emailAddress' =>$soldToAddress->getEmailAddress()

	);
	  $deid_data['soldToAddress']= $sold_to_address_data;
                }
                  return $deid_data;
            }else{
                 return false;
            }
       }else{
           return false;
       }
    } 
    
    /**
     * Get the ecc_return_type product attribute options
     * 
     * @return array
     */
    public function getEccReturnTypeOptions()
    {
        $options = array();
        $eccReturnType = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'ecc_return_type');
        $_options = $eccReturnType->getSource()->getAllOptions();
        foreach ($_options as $option) {
            switch (true) {
                case ($option['label'] == 'Credit'):
                    $options['C'] = $option['label'];
                    break;
                case ($option['label'] == 'Replace'):
                    $options['S'] = $option['label'];
                    break;
            }
        }
        return $options;
    }
    


    public function checkCustomerWarrantyAllowed()
    {
        $session = $this->customerSession;
        $customerId = $session->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $checkCustomer = $customer->getCustomAttribute('ecc_warranty_config');
        $getCustomer = ($checkCustomer) ? $checkCustomer->getValue() : 2;
        if ($getCustomer == "2") {
          return  $this->checkErpWarrantyAllowed();
        } else if ($getCustomer == "1") {
          return true;  
        } else {
          return false;
        }
    }




    /**
     * Checks  ERP level Warranty is enabled or not
     * @return boolean
     */
    public function checkErpWarrantyAllowed()
    {
        
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getWarrantyConfig();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalWarrantyAllowed();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global Toggle Allowed or Not
     * @return boolean
     */
    public function checkGlobalWarrantyAllowed()
    {
        $storeWarrantyallowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DEIU_request/enable_warranty_config',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $storeWarrantyallowed;
    }
    
    /**
     * Checks  BOM allow addition of parts
     * @return boolean
     */
    public function checkBomAllowAdd() {
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        if(!$allowed){
            return 'disable';
        }
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $checkCustomer = !is_null($customer->getEccBomAllowAdd()) ? $customer->getEccBomAllowAdd() : "2";
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpBomAllowAdd();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disable';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = 'disable';
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level BOM allow addition of parts
     * @return boolean
     */
    public function checkErpBomAllowAdd() {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getBomAllowAdd();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalBomAllowAdd();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global BOM allow addition of parts
     * @return boolean
     */
    public function checkGlobalBomAllowAdd() {
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/addition_allowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $allowed;
    }
    
    /**
     * Checks  BOM allow addition of custom parts
     * @return boolean
     */
    public function checkBomAllowCusAdd() {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        if(!$allowed){
            return 'disable';
        }
        if($this->checkBomAllowAdd() == "disable"){
            return 'disable';
        }
        $checkCustomer = !is_null($customer->getEccBomAllowCusAdd()) ? $customer->getEccBomAllowCusAdd() : "2";
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpBomAllowCusAdd();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disable';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = 'disable';
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level BOM allow addition of custom parts
     * @return boolean
     */
    public function checkErpBomAllowCusAdd() {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getBomAllowCustomAdd();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalBomAllowCusAdd();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global BOM allow addition of custom parts
     * @return boolean
     */
    public function checkGlobalBomAllowCusAdd() {
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/addition_custom', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $allowed;
    }

    /**
     * Checks  BOM Allow replacement of original parts
     * @return boolean
     */
    public function checkBomOrigReplace() {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        if(!$allowed){
            return 'disable';
        }
        $checkCustomer = !is_null($customer->getEccBomAllowOrigReplace()) ? $customer->getEccBomAllowOrigReplace(): "2";
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpBomOrigReplace();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disable';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = 'disable';
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level BOM Allow replacement of original parts
     * @return boolean
     */
    public function checkErpBomOrigReplace() {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getBomAllowOrigReplace();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalBomOrigReplace();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global BOM Allow replacement of original parts
     * @return boolean
     */
    public function checkGlobalBomOrigReplace() {
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/replace_orig_allowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $allowed;
    }
    
    /**
     * Checks  BOM Allow replacement with custom parts of original parts
     * @return boolean
     */
    public function checkBomOrigCusReplace() {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        if(!$allowed){
            return 'disable';
        }
        if($this->checkBomOrigReplace() == "disable"){
            return 'disable';
        }
        $checkCustomer = !is_null($customer->getEccBomAllowOrigCusReplace()) ? $customer->getEccBomAllowOrigCusReplace() : "2";
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpBomOrigCusReplace();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disable';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = 'disable';
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level BOM Allow replacement with custom parts of original parts
     * @return boolean
     */
    public function checkErpBomOrigCusReplace() {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getBomAllowOrigCustomReplace();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalBomOrigCusReplace();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global BOM Allow replacement with custom parts of original parts
     * @return boolean
     */
    public function checkGlobalBomOrigCusReplace() {
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/replace_orig_custom', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $allowed;
    }
    
    /**
     * Checks  BOM Allow replacement of modified parts
     * @return boolean
     */
    public function checkBomModReplace() {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        if(!$allowed){
            return 'disable';
        }
        $checkCustomer = !is_null($customer->getEccBomAllowModReplace()) ? $customer->getEccBomAllowModReplace() : "2";
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpBomModReplace();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disable';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = 'disable';
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level BOM Allow replacement of modified parts
     * @return boolean
     */
    public function checkErpBomModReplace() {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getBomAllowModReplace();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalBomModReplace();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global BOM Allow replacement of modified parts
     * @return boolean
     */
    public function checkGlobalBomModReplace() {
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/replace_allowed', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $allowed;
    }
    
    /**
     * Checks  BOM Allow replacement with custom parts of modified parts
     * @return boolean
     */
    public function checkBomModCusReplace() {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        if(!$allowed){
            return 'disable';
        }
        if($this->checkBomModReplace() == "disable"){
            return 'disable';
        }
        $checkCustomer = !is_null($customer->getEccBomAllowModCusReplace()) ? $customer->getEccBomAllowModCusReplace() : "2";
        if ($checkCustomer == "2") {
            $checkGlobal = $this->checkErpBomModCusReplace();
        } else if (($checkCustomer == "0") || (empty($checkCustomer))) {
            $checkGlobal = 'disable';
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = 'disable';
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level BOM Allow replacement with custom parts of modified parts
     * @return boolean
     */
    public function checkErpBomModCusReplace() {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getBomAllowModCustomReplace();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalBomModCusReplace();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global BOM Allow replacement with custom parts of modified parts
     * @return boolean
     */
    public function checkGlobalBomModCusReplace() {
        $allowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/DMAU_request/replace_custom', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $allowed;
    }
    
    public function getBasicInfo(){
        $locationInfo = $this->request->getParam('location');
        $helper = $this->customerconnectHelper;
        $erpAccountNumber = $helper->getErpAccountNumber();
        $locationDetails = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($locationInfo)));
        return $locationDetails;
    }

    /**
     * Validates a Dealer Group code
     *
     * @return array
     */
    public function validateNewGroupCode($request)
    {
        $response = array('error' => 1);
        if ($request->isPost()) {
            $data = $request->getPost();
            if (isset($data['code']) && !empty($data['code'])) {
                $dealerGrp = $this->dealerGroupModelFactory->create()->load($data['code'], 'code');
                if ($dealerGrp->isObjectNew()) {
                    $response['error'] = 0;
                }
            }
        }
        return $response;
    }

    public function checkCusInventorySearch()
    {
        $session = $this->customerSession;
        $customerId = $session->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $checkCustomer = $customer->getCustomAttribute('ecc_inventory_search');
        $getCustomer = ($checkCustomer) ? $checkCustomer->getValue() : 3;
        if ($getCustomer == "3") {
            $this->fetchDealerGroup = 'erp';
            return  $this->checkErpInventorySearch();
        } else {
            $this->fetchDealerGroup = 'customer';
            return $getCustomer;
        }
    }

    public function checkErpInventorySearch()
    {

        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getInventorySearchType();
        if ($checkErp == "3") {
            $this->fetchDealerGroup = 'global';
            $checkGlobal = $this->checkGlobalInventorySearch();
        } else {
            $this->fetchDealerGroup = 'erp';
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    public function checkGlobalInventorySearch()
    {
        $storeWarrantyallowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/dealer_settings/inventory_search_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $storeWarrantyallowed;
    }

    public function checkCusClaimInventorySearch()
    {
        $session = $this->customerSession;
        $customerId = $session->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $checkCustomer = $customer->getCustomAttribute('ecc_claim_inventory_search');
        $getCustomer = ($checkCustomer) ? $checkCustomer->getValue() : 3;
        if ($getCustomer == "3") {
            $this->fetchDealerGroup = 'erp';
            return  $this->checkErpClaimInventorySearch();
        } else {
            $this->fetchDealerGroup = 'customer';
            return $getCustomer;
        }
    }

    public function checkErpClaimInventorySearch()
    {

        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getClaimInventorySearchType();
        if ($checkErp == "3") {
            $this->fetchDealerGroup = 'global';
            $checkGlobal = $this->checkGlobalClaimInventorySearch();
        } else {
            $this->fetchDealerGroup = 'erp';
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    public function checkGlobalClaimInventorySearch()
    {
        $storeWarrantyallowed = $this->scopeConfig->getValue('dealerconnect_enabled_messages/dealer_settings/claim_inventory_search_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $storeWarrantyallowed;
    }

    public function getDealerGroup()
    {
        $inventoryType = $this->checkCusInventorySearch();
        $globalVal = $this->scopeConfig->getValue('dealerconnect_enabled_messages/dealer_settings/inventory_dealer_groups',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        switch ($this->fetchDealerGroup) {
            case "customer":
                $session = $this->customerSession;
                $customerId = $session->getCustomer()->getId();
                $customer = $this->customerRepository->getById($customerId);
                $commHelper = $this->commHelper;
                /* @var $helper Epicor_Comm_Helper_Data */
                $erpAccount = $commHelper->getErpAccountInfo();
                /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
                $dealerGroupVal = $customer->getCustomAttribute('ecc_dealer_group')->getValue();
                $dealerGroupVal = $dealerGroupVal == '0' ? ($erpAccount->getInventoryDealerGroups() == '0' ? $globalVal : $erpAccount->getInventoryDealerGroups()) : $dealerGroupVal;
                break;
            case "erp":
                $commHelper = $this->commHelper;
                /* @var $helper Epicor_Comm_Helper_Data */
                $erpAccount = $commHelper->getErpAccountInfo();
                /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
                $dealerGroupVal = $erpAccount->getInventoryDealerGroups();
                $dealerGroupVal = $dealerGroupVal == '0' ? $globalVal : $dealerGroupVal;
                break;
            case "global":
                $dealerGroupVal = $globalVal;
                break;
        }
        return $dealerGroupVal;

    }

    public function getClaimDealerGroup()
    {
        $inventoryType = $this->checkCusClaimInventorySearch();
        $globalVal = $this->scopeConfig->getValue('dealerconnect_enabled_messages/dealer_settings/claim_inventory_dealer_groups',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        switch ($this->fetchDealerGroup) {
            case "customer":
                $session = $this->customerSession;
                $customerId = $session->getCustomer()->getId();
                $customer = $this->customerRepository->getById($customerId);
                $commHelper = $this->commHelper;
                /* @var $helper Epicor_Comm_Helper_Data */
                $erpAccount = $commHelper->getErpAccountInfo();
                /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
                $dealerGroupVal = $customer->getCustomAttribute('ecc_claim_dealer_group')->getValue();
                $dealerGroupVal = $dealerGroupVal == '0' ? ($erpAccount->getClaimInventoryDealerGroups() == '0' ? $globalVal : $erpAccount->getClaimInventoryDealerGroups()) : $dealerGroupVal;
                break;
            case "erp":
                $commHelper = $this->commHelper;
                /* @var $helper Epicor_Comm_Helper_Data */
                $erpAccount = $commHelper->getErpAccountInfo();
                /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
                $dealerGroupVal = $erpAccount->getClaimInventoryDealerGroups();
                $dealerGroupVal = $dealerGroupVal == '0' ? $globalVal : $dealerGroupVal;
                break;
            case "global":
                $dealerGroupVal = $globalVal;
                break;
        }
        return $dealerGroupVal;
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    public function  getCustomerconnectHelper()
    {
        return $this->customerconnectHelper;
    }

    public function claimStatusDataMappingExists($all = false)
    {
        $collection = $this->dataMappingCollectionFactory->create();
        $collection->addFieldToFilter('message', "DCLD");
        if ($all == false) {
            $collection->addFieldToFilter('orignal_tag', 'claim>claimStatus');
            if ($collection->count()) {
                return true;
            }
        } else {
            $where = [
                        'in'=> [
                            'claim>claimStatus',
                            'claim>claimUpdateDueDate',
                            'claim>claimStatusChangeDate'
                        ]
                    ];
            $collection->addFieldToFilter('orignal_tag', $where);
            if ($collection->count() >= 3) {
                return true;
            }
        }
        return false;
    }

}
