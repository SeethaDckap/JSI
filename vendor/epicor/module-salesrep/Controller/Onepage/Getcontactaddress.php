<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Onepage;

class Getcontactaddress extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    protected $billingAddressids = [];
    protected $shippingAddressids =[];

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $customerCustomerFactory;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;



    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->commHelper = $commHelper;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        parent::__construct($context);
    }
    public function restrictAddressTypes()
    {
        $forceAddressType = 0;
                $isCustomer = 0;
                if ($this->customerSession->isLoggedIn()) {
                    $isCustomer = 1;
                    if ($this->scopeConfig->getValue('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                        $forceAddressType = 1;
                    } else {
                        $forceAddressType = 0;
                    }
                }
                
        return $forceAddressType;
    }
    
    public function execute()
    {
        $salesrep_contact = $this->getRequest()->getParam('salesrep_contact', false);  
        $type = $this->getRequest()->getParam('type', false);    
        $choose = $this->getRequest()->getParam('choose', false);        
        
        $salesRepInfo = '';
        $salesRepCustomerId = '';
        $addressData = [];
        $quote = $this->checkoutSession->getQuote();
        $helper = $this->commHelper;
        $customer =  $this->customerSession->getCustomer();
        if($quote) {
            
            if(!$salesrep_contact && $choose) {                
                    $quote->setEccSalesrepChosenCustomerId(0);
                    $quote->setEccSalesrepChosenCustomerInfo(null);
            }
            if($salesrep_contact) {
                    $salesRepInfo = base64_decode($salesrep_contact);
                    $salesRepData = unserialize($salesRepInfo);
                    /* @var $helper Epicor_Comm_Helper_Data */

                    $erpAccount = $helper->getErpAccountInfo();

                    if (!empty($salesRepData['ecc_login_id'])) {
                        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
                        $collection->addAttributeToFilter('ecc_contact_code', $salesRepData['contact_code']);
                        $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccount->getId());
                        $collection->addFieldToFilter('website_id', $this->storeManager->getStore()->getWebsiteId());
                        $customer = $collection->getFirstItem();
                        $salesRepCustomerId = $customer->getId();
                    }
                    

                    $customerSession = $this->customerSession;
                    /* @var $customerSession Mage_Customer_Model_Session */

                    $customer = $customerSession->getCustomer();
                    /* @var $customer Epicor_Comm_Model_Customer */

                    $quote->setEccSalesrepCustomerId($customer->getId());
                    $quote->setEccSalesrepChosenCustomerId($salesRepCustomerId);
                    $quote->setEccSalesrepChosenCustomerInfo($salesRepInfo);

            }
            $quote->save();
            $customer =  $this->customerSession->getCustomer();
            if ($this->customerSession->isLoggedIn() && $customer->isSalesRep() && $helper->isMasquerading()) {
     
                
                $restrict = $this->restrictAddressTypes();
              //  $type = $observer->getEvent()->getType();
            
                if ($quote->getEccSalesrepChosenCustomerId()) {                 
                    $type = 'delivery';
                    $addressData[$type] = $this->getAddressArray($type);
                    $type = 'invoice';
                    $addressData[$type] = $this->getAddressArray($type);

                }elseif ($quote->getEccSalesrepChosenCustomerInfo()) {                
                    $type = 'delivery';
                    $addressData[$type] = $this->getAddressArrayinfo($type);
                    $type = 'invoice';
                    $addressData[$type] = $this->getAddressArrayinfo($type);
                }else{
                    $type = 'invoice';
                    $addressData[$type] = $this->getAddressArrayinfo($type);
                }
            }   
            $addressData['selected_contact'] = $quote->getEccSalesrepChosenCustomerInfo();
            $addressData['shippingAddressids'] = $this->shippingAddressids;
            $addressData['billingAddressids'] = $this->billingAddressids;
        }
            $this->getResponse()->setBody($this->jsonHelper->jsonEncode($addressData));
            //M1 > M2 Translation End
        }

    /**
     * Set additional customer address data
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    protected function getAddressArrayinfo($type)
    {
        $addressData = [];
        /* @var $salesRepCustomer Epicor_Comm_Model_Customer */

        $quote = $this->checkoutSession->getQuote();
        $restrict = $this->restrictAddressTypes();
        $customerId = $quote->getEccSalesrepChosenCustomerId();
        $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);        
        
        $customerInfo = unserialize($quote->getEccSalesrepChosenCustomerInfo());
        if (isset($customerInfo['name'])) {

            $salesRepCustomer = $this->customerCustomerFactory->create();
            /* @var $salesRepCustomer Epicor_Comm_Model_Customer */
            $nameParts = explode(' ', $customerInfo['name'], 3);
            $salesRepCustomer->setEmail($customerInfo['email']);
        }else{
            $nameParts[0] ='';
            $nameParts[1] ='';
            $nameParts[2] ='';
        }

            $salesRepCustomer->setFirstname($nameParts[0]);

            if (count($nameParts) == 3) {
                $salesRepCustomer->setMiddlename($nameParts[1]);
                $salesRepCustomer->setLastname($nameParts[2]);
            } else {
                $salesRepCustomer->setLastname($nameParts[1]);
            }
            $erpAddresses = ($restrict) ? $salesRepCustomer->getAddressesByType($type) : $salesRepCustomer->getCustomAddresses();
            if($type =='invoice'){
                $erpInvoiceAddresses = $salesRepCustomer->getCustomAddresses();
                foreach ($erpInvoiceAddresses as $address) {
                    $addressData[$address->getId()] = $address->getData();
                    $addressData[$address->getId()]['customer_id'] = $address->getParentId();
                    $addressData[$address->getId()]['street'] = $address->getStreet();
                    $addressData[$address->getId()]['default_billing'] = $address->isDefaultBilling();
                    $addressData[$address->getId()]['inline'] =  $this->getCustomerAddressInline($address,$type); 
                    $this->billingAddressids[]=$address->getId();    
                }
            }
            foreach ($erpAddresses as $address) {
                /* @var $address Mage_Customer_Model_Address */
             //   $address->setId('customeraddress_' . $address->getId());
                if($type =='delivery' && $address->getData('is_custom')){
                    $addressData[$address->getId()] = $address->getData();
                    $addressData[$address->getId()]['customer_id'] = $address->getParentId();
                    $addressData[$address->getId()]['street'] = $address->getStreet();
                    $addressData[$address->getId()]['inline'] =  $this->getCustomerAddressInline($address,$type);
                    $this->shippingAddressids[]=$address->getId();
                }elseif($type =='invoice' && $address->getData('is_custom')){
                    $addressData[$address->getId()] = $address->getData();
                    $addressData[$address->getId()]['customer_id'] = $address->getParentId();
                    $addressData[$address->getId()]['street'] = $address->getStreet();
                    $addressData[$address->getId()]['default_billing'] = $address->isDefaultBilling();
                    $addressData[$address->getId()]['inline'] =  $this->getCustomerAddressInline($address,$type);
                    $this->billingAddressids[]=$address->getId();                            
                }
            }
        
                    
        return $addressData;     

        
    }
    protected function getAddressArray($type)
    {
        $addressData = [];
        /* @var $salesRepCustomer Epicor_Comm_Model_Customer */

        $quote = $this->checkoutSession->getQuote();
        $restrict = $this->restrictAddressTypes();
        $customerId = $quote->getEccSalesrepChosenCustomerId();
        $salesRepCustomer = $this->customerCustomerFactory->create()->load($customerId);
        
        $collection = $salesRepCustomer->getAddressCollection()
            ->setCustomerFilter($salesRepCustomer)
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('ecc_is_invoice', 'left')
            ->addAttributeToSelect('ecc_is_delivery', 'left')
            ->addExpressionAttributeToSelect(
            'is_custom', 'IF((NOT(`at_ecc_is_invoice`.value <=> 1) AND NOT(`at_ecc_is_delivery`.value <=> 1) AND NOT(`at_ecc_is_registered`.value <=> 1)), 1 , 0)', array('ecc_is_invoice', 'ecc_is_delivery', 'ecc_is_registered')
        );

        if ($restrict) {
            $collection->getSelect()
                ->where(
                    '(`at_ecc_is_' . $type . '`.value = 1) ' .
                    'OR (NOT(`at_ecc_is_invoice`.value <=> 1) AND NOT(`at_ecc_is_delivery`.value <=> 1) AND NOT(`at_ecc_is_registered`.value <=> 1))'
            );
        }
        $customerInfo = unserialize($quote->getEccSalesrepChosenCustomerInfo());
        $nameParts = explode(' ', $customerInfo['name'], 3);
        $salesRepCustomer->setEccErpAccountType('salesrep');
        if($type =='invoice'){
            $erpInvoiceAddresses = $salesRepCustomer->getCustomAddresses();
            foreach ($erpInvoiceAddresses as $address) {
                
            /* @var $address Mage_Customer_Model_Address */
         //   $address->setId('customeraddress_' . $address->getId());
                $address->setFirstname($nameParts[0]);

                if (count($nameParts) == 3) {
                    $address->setMiddlename($nameParts[1]);
                    $address->setLastname($nameParts[2]);
                } else {
                    $address->setLastname($nameParts[1]);
                }
              $salesRepCustomer->setEmail($customerInfo['email']);
              
                $addressData[$address->getId()] = $address->getData();
                $addressData[$address->getId()]['customer_id'] = $address->getParentId();
                $addressData[$address->getId()]['street'] = $address->getStreet();
                $addressData[$address->getId()]['default_billing'] = $address->isDefaultBilling();
                $addressData[$address->getId()]['inline'] =  $this->getCustomerAddressInline($address,$type);
                $this->billingAddressids[]=$address->getId();     
            }
        }
        foreach ($collection->getItems() as $address) {
            /* @var $address Mage_Customer_Model_Address */
         //   $address->setId('customeraddress_' . $address->getId());
                $address->setFirstname($nameParts[0]);

                if (count($nameParts) == 3) {
                    $address->setMiddlename($nameParts[1]);
                    $address->setLastname($nameParts[2]);
                } else {
                    $address->setLastname($nameParts[1]);
                }
              $salesRepCustomer->setEmail($customerInfo['email']);
              // $address->setData('street',$address->getStreet());
            if($type =='delivery' && $address->getData('is_custom')){
                $addressData[$address->getId()] = $address->getData();
                $addressData[$address->getId()]['customer_id'] = $address->getParentId();
                $addressData[$address->getId()]['street'] = $address->getStreet();
                $addressData[$address->getId()]['inline'] =  $this->getCustomerAddressInline($address,$type);
                $this->shippingAddressids[]=$address->getId();
            }elseif($type =='invoice' && $address->getData('is_custom')){
                $addressData[$address->getId()] = $address->getData();
                $addressData[$address->getId()]['customer_id'] = $address->getParentId();
                $addressData[$address->getId()]['street'] = $address->getStreet();
                $addressData[$address->getId()]['default_billing'] = $address->isDefaultBilling();
                $addressData[$address->getId()]['inline'] =  $this->getCustomerAddressInline($address,$type);
                $this->billingAddressids[]=$address->getId();                            
            }
        }
                    
        return $addressData;     
    }
    private function getCustomerAddressInline($address,$type)
    {
        $address->setDefaultShipping(false);
        $builtOutputAddressData = $this->addressMapper->toFlatArray($address);
        $render = $this->addressConfig->getFormatByCode(\Magento\Customer\Model\Address\Config::DEFAULT_ADDRESS_FORMAT);
        if($type == 'invoice'){
            $render->setDefaultFormat(
                '{{var street}}, {{var city}}, {{var region}} {{var postcode}}, {{var country}}'
            );
        }
        return $render->getRenderer()
            ->renderArray($builtOutputAddressData);
    }
 }
