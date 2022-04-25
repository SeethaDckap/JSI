<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Add;


/**
 * Order Details page title
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Inventory extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;    

     /**
      * @var \Magento\Directory\Model\Config\Source\Country
      */
     protected $country;

     /**
      * @var Magento\Directory\Model\ResourceModel\Country\CollectionFactory
      */
     protected $countryCollectionFactory;

     /**
      * @var \Magento\Directory\Model\CountryFactory
      */
     protected $directoryCountryFactory;

     /**
      * @var \Magento\Directory\Model\RegionFactory
      */
    protected $directoryRegionFactory;          

    /**
     *
     * @var \Magento\Framework\App\Request\Http 
     */ 
    protected $request;

    /**
     *
     * @var \Magento\Framework\DataObjectFactory 
     */
    private $dataObjectFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;         

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;    

    /**
     *
     * @var \Epicor\Dealerconnect\Model\ResourceModel\Warranty\CollectionFactory 
     */
    protected $warrantyCollectionFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @var \Epicor\Dealerconnect\Helper\Data 
     */
    protected $dealerconnectHelper;
    
    protected $_addressData;
        
    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Directory\Model\Config\Source\Country $country
     * @param \Magento\Directory\Model\CountryFactory $directoryCountryFactory
     * @param \Magento\Directory\Model\RegionFactory $directoryRegionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Epicor\Comm\Helper\Messaging $commMessagingHelper
     * @param \Epicor\Dealerconnect\Model\ResourceModel\Warranty\CollectionFactory $warrantyCollectionFactory
     * @param \Epicor\Dealerconnect\Helper\Data $dealerconnectHelper
     * @param array $data
     */
    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Directory\Model\Config\Source\Country $country,
            \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
            \Magento\Directory\Model\RegionFactory $directoryRegionFactory,            
            \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory,
            \Magento\Framework\DataObjectFactory $dataObjectFactory,
            \Magento\Framework\App\Request\Http $request,
            \Epicor\Comm\Helper\Messaging $commMessagingHelper,
            \Epicor\Dealerconnect\Model\ResourceModel\Warranty\CollectionFactory $warrantyCollectionFactory,
            \Epicor\Dealerconnect\Helper\Data $dealerconnectHelper,
            \Magento\Directory\Helper\Data $directoryHelper,
            \Magento\Framework\Json\EncoderInterface $jsonEncoder,
            \Magento\Framework\App\Cache\Type\Config $configCacheType,
            \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
            \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
            \Magento\Customer\Model\Session $customerSession,
            array $data = []
        )
        {
            $this->countryCollectionFactory = $collectionFactory;
            $this->directoryCountryFactory = $directoryCountryFactory;
            $this->directoryRegionFactory = $directoryRegionFactory;  
            $this->scopeConfig = $context->getScopeConfig();
            $this->registry = $registry;
            $this->request = $request;
            $this->warrantyCollectionFactory = $warrantyCollectionFactory;
            $this->dataObjectFactory = $dataObjectFactory;
            $this->commMessagingHelper = $commMessagingHelper;
            $this->dealerconnectHelper = $dealerconnectHelper;
            $this->country = $country;
            $this->customerSession = $customerSession;
            $this->_addressData = [];
            $this->setDealerAddress();
            parent::__construct(
                $context,
                $directoryHelper,
                $jsonEncoder,
                $configCacheType,
                $regionCollectionFactory,
                $countryCollectionFactory,
                $data
            );
    }

    public function getConfigFlag($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
    }
    
    public function getCountryCollection()
    {
        return $this->countryCollectionFactory->create();
    }  

    public  function getListOfWarrantyCodes() {
        $collection = $this->warrantyCollectionFactory->create();
        $collection->addFieldToFilter('status', 'yes');
        $collection->addFieldToFilter('description',array('neq' => ''));
        $countItems = $collection->getItems();
        return $countItems;
    }    


    public function checkWarrantyEnabledOrNot()
    {
        return $this->dealerconnectHelper->checkCustomerWarrantyAllowed();
    }
    
    public function addInventoryUrl()
    {
        return $this->getUrl('dealerconnect/inventory/addInventory');
    }
    
     public function getAddressesHtmlSelect()
    {

        $options = array();

        $helper = $this->commMessagingHelper;
        $customer = $this->customerSession->getCustomer();
        /* @var $customer \Epicor\Dealerconnect\Model\Customer */
        if (!$customer->getId()) {
            $customer = $customer->load($this->customerSession->getId());
            $this->customerSession->setCustomer($customer);
        }

        $addressId = null;
        $addresses = ($this->restrictAddressTypes()) ?
            $customer->getAddressesByType('delivery') : $customer->getCustomAddresses();
        foreach ($addresses as $address) {
            
            /* @var $address \Epicor\Comm\Model\Customer\Address */
            $formatted = trim(ltrim(trim(str_replace($customer->getName() . ',', $address->getCompany() . ',', $address->format('oneline'))), ','));
            $street1 = isset($address->getStreet()[0]) ? $address->getStreet()[0] : '';
            $street2 = isset($address->getStreet()[1]) ? $address->getStreet()[1] : '';
            $street3 = isset($address->getStreet()[2]) ? $address->getStreet()[2] : '';
            $options[] = array(
                'value' => $address->getId(),
                'label' => $formatted,
                'params' => array(
                    'data-iscustom' => $address->getIsCustom(),
                    'data-address' => htmlentities(json_encode([
                        'addressCode' => $address->getEccErpAddressCode(),
                        'firstname' => $helper->stripNonPrintableChars($address->getFirstname()),
                        'lastname' => $helper->stripNonPrintableChars($address->getLastname()),
                        'address1' => $helper->stripNonPrintableChars($street1),
                        'address2' => $helper->stripNonPrintableChars($street2),
                        'address3' => $helper->stripNonPrintableChars($street3),
                        'city' => $helper->stripNonPrintableChars($address->getCity()),
                        'county' => $helper->stripNonPrintableChars($helper->getRegionNameOrCode($address->getCountry_id(), ($address->getRegionId() ? $address->getRegionId() : $address->getRegion()))),
                        'country' => $helper->getErpCountryCode($address->getCountry_id()),
                        'postcode' => $helper->stripNonPrintableChars($address->getPostcode()),
                        'telephoneNumber' => $helper->stripNonPrintableChars($address->getTelephone()),
                        'mobileNumber' => $helper->stripNonPrintableChars($address->getEccMobileNumber()),
                        'faxNumber' => $helper->stripNonPrintableChars($address->getFax()),
                        'company' => $helper->stripNonPrintableChars($address->getCompany())
                        ]
                        )
                    )
                )
            );
            if ($this->_addressData->getAddressCode() == $address->getEccErpAddressCode()) {
                $addressId = $address->getId();
            }
        }

        if ($this->_addressData->getAddressCode() == '') {
            $options[] = array(
                'value' => '',
                'label' => '',
                'params' => array(
                    'address_data' => $this->_addressData->getData(),
                    'id' => 'custom_address_selected',
                    'selected' => 'selected',
                    'full_country' => $this->getCountry(),
                    'data-address' => htmlentities(json_encode($this->_addressData->getData()))
                )
            );
        }
        $select = $this->getLayout()->createBlock('\Magento\Framework\View\Element\Html\Select')
            ->setName('dealer_address_id')
            ->setId('dealer-address-select')
            ->setClass('address-select')
            ->setValue($addressId)
            ->setOptions($options);

        $html = $select->getHtml();

        return $html;
    }
    
    public function setDealerAddress()
    {
        $customer = $this->customerSession->getCustomer();
         $addresses = ($this->restrictAddressTypes()) ?
            $customer->getAddressesByType('delivery') :
            $customer->getAddressesByType('registered');

        if (!empty($addresses)) {
            $address = array_pop($addresses);
        } else {
            $address = $customer->getDefaultBillingAddress();
        }
        /* @var $data \Magento\Customer\Model\Address */
        $this->_addressData = $this->dataObjectFactory->create(
            [
                'data' => array(
                    'firstname' => $address->getFirstname(),
                    'lastname' => $address->getLastname(),
                    'company' => $address->getCompany(),
                    'address1' => $address->getStreet()[0],
                    'address2' => isset($address->getStreet()[1]) ? $address->getStreet()[1] : '',
                    'address3' => isset($address->getStreet()[2]) ? $address->getStreet()[2] : '',
                    'city' => $address->getCity(),
                    'county' => $address->getCounty() ?: $address->getRegionCode(),
                    'region' => $address->getRegion(),
                    'region_id' => $address->getRegionId(),
                    'country' => $address->getCountry(),
                    'postcode' => $address->getPostcode(),
                    'email' => $address->getEccEmail(),
                    'telephone_number' => $address->getTelephone(),
                    'fax' => $address->getFax(),
                    'address_code' => $address->getEccErpAddressCode(),
                    'instructions' => $address->getEccInstructions()
                )
            ]
        );
        return $this;
    
    }
    
    public function getAddressCode()
    {
        return $this->_addressData ? $this->_addressData->getAddressCode() : null;
    }

    public function getFirstname()
    {
        if($this->_addressData){ 
            return $this->_addressData->getFirstname();
        }    
    }
    
    public function getLastname()
    {
        if($this->_addressData){ 
            return $this->_addressData->getLastname();
        }    
    }

    public function getAddress($_i = 1)
    {
        if($this->_addressData){  
            $var = 'address' . $_i;
            return $this->_addressData ? $this->_addressData->getData($var) : null;
        }
    }

    public function getCity()
    {
        if($this->_addressData){
            return $this->_addressData ? $this->_addressData->getCity() : null;
        }
    }

    public function getCounty()
    {
        $helper = $this->commMessagingHelper;
        if($this->_addressData){            
            $region = $helper->getRegionFromCountyName($this->getCountryCode(), $this->_addressData->getCounty());
            return ($region) ? $region->getName() : $this->_addressData->getCounty();
        }else
            return null;
    }

    public function getRegionId()
    {
        $helper = $this->commMessagingHelper;
            $region = $this->_addressData ? $helper->getRegionFromCountyName($this->getCountryCode(), $this->_addressData->getCounty()) : null;

        $regionId = ($region) ? $region->getId() : 0;
        return $regionId;
    }

    public function getPostcode()
    {
        return $this->_addressData ? $this->_addressData->getPostcode(): null;
    }

    public function getCountryCode()
    {
        $helper = $this->commMessagingHelper;
        if($this->_addressData){   
            $this->_countryCode = $helper->getCountryCodeForDisplay($this->_addressData->getCountry(), $helper::ERP_TO_MAGENTO);
        }

        return $this->_countryCode;
    }

    public function getCountry()
    {
        try {
            $helper = $this->commMessagingHelper;

            return $helper->getCountryName($this->getCountryCode());
        } catch (\Exception $e) {
            if($this->_addressData){    
                return $this->_addressData ? $this->_addressData->getCountry() : null;
            }
        }
    }

    public function getTelephoneNumber()
    {
        return $this->_addressData ? $this->_addressData->getTelephoneNumber() : null;
    }

    public function getFaxNumber()
    {
        return $this->_addressData ? $this->_addressData->getFaxNumber() : null;
    }

    public function getEmail()
    {
        return $this->_addressData ? $this->_addressData->getEmailAddress() : null;
    }
    
    public function getCompany()
    {
        return $this->_addressData ? $this->_addressData->getCompany() : null;
    }

    /**
     * @return bool
     */
    public function restrictAddressTypes()
    {
        return $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
