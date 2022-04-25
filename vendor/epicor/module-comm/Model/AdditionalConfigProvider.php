<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;

use Epicor\Comm\Model\Customer\Address;
use Epicor\Comm\Model\Customer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;

class AdditionalConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {

    const LIMIT_SHIPPING_ADDRESS_CONFIG = 'customer/address/limit_shipping_addresses';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    protected $customerCustomerFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $contactModel;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    protected $listsFrontendContractHelper;
    
    
    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;  


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        HttpContext $httpContext,
        CustomerRepository $customerRepository,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Epicor\SalesRep\Model\Checkout\Contact $contact,
        CheckoutSession $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->customerRepository = $customerRepository;
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->contactModel = $contact;
        $this->request = $request;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->branchPickupHelper = $branchPickupHelper;
    }

    public function getConfig() {

        $customer = $this->customerSession->getCustomer();
        $forceAddressType = 0;
        $isCustomer = 0;
        if ($this->isCustomerLoggedIn()) {
            $isCustomer = 1;
            if ($this->scopeConfig->getValue('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && 
                    ($customer->getEccErpaccountId() || $this->isSalesRep() )) {
                $forceAddressType = 1;
            } else {
                $forceAddressType = 0;
            }
        }
        
        $output['forceAddressTypes'] = $forceAddressType;
        $output['isShippingIds'] = $this->getAddressIds($forceAddressType, 'delivery');
        $isContractEnabled = $this->listsFrontendContractHelper->contractsEnabled();
        $output['isContractEnabled'] = $isContractEnabled;
        $output['isAddressLimited'] = $this->isShippingAddressLimited();
        $output['maxAddresses'] = $this->getAddressLimit();
        $output['defaultShippingId'] = $this->getDefaultShippingId($customer);
        if($isContractEnabled){
            $output['isShippingIds'] = $this->getContractAddress($output['isShippingIds']);
        }
        $output['isBillingIds'] = $this->getAddressIds($forceAddressType, 'invoice');
        $output['isRegIds'] = $this->getAddressIds($forceAddressType, 'registered');
        $output['selectShippingAddress'] = $this->getSelectedShippingAddress();
        $output['isCustomer'] = $isCustomer;
        $output['isSalesRep'] = $this->isSalesRep();
        $output['isB2BHierarchyMasquerade'] = $this->isB2BHierarchyMasquerade();
        $output['branchpickupEnabled'] = $this->branchPickupHelper->isBranchPickupAvailable();
        $controller = $this->request->getControllerName();
        $route = $this->request->getRouteName();

        if(($route == 'checkout' && $controller != 'cart')){
            $output['isSalesRepContactenabled'] = $this->isContactEnable();
            $output['isSalesRepContactReq'] = $this->getContactReq();            
        }
        $output['ErpShippingCanAddNew'] = $this->canAddNew('shipping');
        $output['ErpBillingCanAddNew'] = $this->canAddNew('billing');
        $output['isMasqurading'] = $this->commHelper->isMasquerading();
        $output['customerEccErpaccountId'] = $customer->getEccErpaccountId();
        $output['eccCustomerOrderRefValidation'] = $this->commHelper->cusOrderRefValidation();
        $output['eccNonErpProductsActive'] = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/enabled');
        $output['homeCustomerRegistrationEnabled'] = $this->showCustomerRegistration();
        $output['isHidePrices'] = $this->commHelper->getEccHidePrice();
        $output['maxCommentLength'] = $this->scopeConfig->getValue('checkout/options/max_comment_length',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $output['limitCommentLength'] = $this->scopeConfig->getValue('checkout/options/limit_comment_length',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $output['isPriceDisplayDisabled'] = $this->commHelper->isPriceDisplayDisabled();
        return $output;
    }

    private function isShippingAddressLimited(): bool
    {
        $addressLimit = $this->getAddressLimit();
        return $addressLimit > 0;
    }

    private function getAddressLimit()
    {
        return $this->scopeConfig->getValue(self::LIMIT_SHIPPING_ADDRESS_CONFIG);
    }

    private function getDefaultShippingId($customer)
    {
        if ($customer instanceof Customer) {
            $address = $customer->getDefaultShippingAddress();

            return $address instanceof Address ? $address->getEntityId() : '';
        }
    }

    public function getContractAddress($shippingids)
    {
        $helper = $this->listsFrontendContractHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Contract */
         
        /* @var $quote Epicor_Comm_Model_Quote */

         $quote = $this->checkoutSession->getQuoteOnly();

        $addressKeys = [];

        if (($helper->contractsDisabled()) || (empty($quote))) {
            return $shippingids;
        }
 
        $contracts = $helper->getQuoteContracts($quote);
        /* @var $addresses Varien_Object */
        if ($contracts) {
            $customerSession = $this->customerSession;
            /* @var $customerSession Mage_Customer_Model_Session */
            $customer = $customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */
            $customerAddresses = $customer->getAddressesByType('delivery');
            $contractAddresses = $helper->getValidShippingAddressCodesForContracts($contracts);

            $filteredAddresses = array();
            $returncontractadd= FALSE;
            foreach ($customerAddresses as $address) {
                if (in_array($address->getEccErpAddressCode(), $contractAddresses) &&
                    in_array($address->getId(), $shippingids)
                ) {
                    array_push($addressKeys, $address->getId());
                    $returncontractadd= TRUE;
                }
            }
            return $addressKeys;  
        }
        return $shippingids;
    }  

    private function isContactEnable() {
        if($this->contactModel->isShow()  && $this->isSalesRep()){
            return 1;
        }
            return 0;
        
    }

    private function getContactReq() {
        if($this->contactModel->isShow()  && $this->contactModel->isRequired()){
            return true;
        }
            return false;
        
    }
    
    private function isSalesRep() {
        
        
        $customer =  $this->customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

            $addressData = array();
        if ($this->isCustomerLoggedIn() && $customer->isSalesRep() && $helper->isMasquerading()) {
            
            return 1;
        }
        //print_r($addressData); exit; 
        return 0;
        
    }

    /**
     * Validate B2B is Masquerading.
     *
     * @return boolean
     */
    private function isB2BHierarchyMasquerade()
    {
        $helper = $this->commHelper;
        /* @var $helper \Epicor\Comm\Helper\Data */

        $b2bHierarchyMasquerade
            = $this->customerSession->getB2BHierarchyMasquerade();

        if ($this->isCustomerLoggedIn() && $helper->isMasquerading()
            && $b2bHierarchyMasquerade
        ) {
            return 1;
        }

        return 0;
    }



    private function getAddressIds($forceAddressType, $type = 'delivery') {
        
        $addressKeys = [];
        if ($this->isCustomerLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            if ($this->isSalesRep()) {
                $addresses = $this->getAddressesIdData($forceAddressType, $type);
                return $addresses;
            } else {
                $addresses = ($forceAddressType) ? $customer->getAddressesByType($type) : $customer->getAddresses();
            }

            foreach ($addresses as $item) {
                array_push($addressKeys, $item->getId());
            }
        }
        return $addressKeys;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     * @codeCoverageIgnore
     */
    private function isCustomerLoggedIn() {
        return (bool) $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Get selected address ID
     *
     * @return string
     */
    private function getSelectedShippingAddress() {
        $quote = $this->checkoutSession->getQuoteOnly();
        $shippingAddressId = $quote->getShippingAddress()->getCustomerAddressId();
        return $shippingAddressId;
    }


    /**
     * Can Add new address.
     *
     * @param string $type Address type.
     *
     * @return mixed
     */
    public function canAddNew(string $type='shipping')
    {
        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        return $helper->customerAddressPermissionCheck('create', 'customer', $type);

    }//end canAddNew()


    public function showCustomerRegistration()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * Get Address Ids
     *
     * @param integer     $forceAddressType Force Address Type.
     * @param string|null $type             Address type delivery|invoice|registered.
     *
     * @return array
     */
    public function getAddressesIdData(int $forceAddressType=0, $type=null)
    {
        $erpAccount = $this->commHelper->getErpAccountInfo();
        if ($erpAccount) {
            return $forceAddressType ? $erpAccount->getAddressIds($type) : $erpAccount->getAddressIds();
        }

    }//end getAddressesIdData()


}
