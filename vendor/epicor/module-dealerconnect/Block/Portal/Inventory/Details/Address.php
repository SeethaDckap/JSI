<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Details;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method void setOnRight(bool $bool)
 * @method bool getOnRight()
 */
class Address extends \Magento\Directory\Block\Data
{

    /**
     *  @var \Magento\Framework\DataObject 
     */
    protected $_addressData;
    protected $_ownerAddressData;
    protected $_soldAddressData;
    protected $_LocationAddressData;
    protected $_countryCode;
    protected $_showAddressCode = true;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commonHelper = $commonHelper;
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


    public function _construct()
    {
        parent::_construct();
        $this->_addressData = array();
        $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/deid/address.phtml');
        $this->setOnRight(false);
        $this->setShowName(true);
    }

    public function getJsonEncodedData()
    {
        if(empty($this->_addressData)){
            return json_encode(array());
        }
        $detailsArray = array(
            'name' => $this->_addressData->getName(),
            //M1 > M2 Translation Begin (Rule 9)
            /*'address1' => $this->_addressData->getAddress1(),
            'address2' => $this->_addressData->getAddress2(),
            'address3' => $this->_addressData->getAddress3(),*/
            'address1' => $this->_addressData->getData('address1'),
            'address2' => $this->_addressData->getData('address2'),
            'address3' => $this->_addressData->getData('address3'),
            //M1 > M2 Translation End
            'city' => $this->_addressData->getCity(),
            'county' => $this->_addressData->getCounty(),
            'country' => $this->_addressData->getCountry(),
            'postcode' => $this->_addressData->getPostcode(),
            'email' => $this->_addressData->getEmailAddress(),
            'telephone' => $this->_addressData->getTelephoneNumber(),
            'fax' => $this->_addressData->getFaxNumber(),
            'address_code' => $this->_addressData->getAddressCode()
        );
        return json_encode($detailsArray);
    }

    public function getAddressCode()
    {
        return $this->_addressData->getAddressCode();
    }

    public function getName()
    {
        return $this->_addressData->getName();
    }

    public function getCompany()
    {
        return $this->_addressData->getCompany();
    }

    public function getAddress1()
    {
        //M1 > M2 Translation Begin (Rule 9)
        //return $this->_addressData->getAddress1();
        return $this->_addressData->getData('address1');
        //M1 > M2 Translation End
    }

    public function getAddress2()
    {
        //M1 > M2 Translation Begin (Rule 9)
        //return $this->_addressData->getAddress2();
        return $this->_addressData->getData('address2');
        //M1 > M2 Translation End
    }

    public function getAddress3()
    {
        //M1 > M2 Translation Begin (Rule 9)
        //return $this->_addressData->getAddress3();
        return $this->_addressData->getData('address3');
        //M1 > M2 Translation End
    }

    public function getStreet()
    {
        $street = $this->_addressData->getData('address1');
        $street .= $this->_addressData->getData('address2') ? ', ' . $this->_addressData->getData('address2') : '';
        $street .= $this->_addressData->getData('address3') ? ', ' . $this->_addressData->getData('address3') : '';
        //M1 > M2 Translation End
        return $street;
    }

    public function getCity()
    {
        return $this->_addressData->getCity();
    }

    public function getCounty()
    {
        $helper = $this->customerconnectHelper;
        $region = $helper->getRegionFromCountyName($this->getCountryCode(), $this->_addressData->getCounty());

        return ($region) ? $region->getName() : $this->_addressData->getCounty();
    }

    public function getRegionId()
    {
        $helper = $this->customerconnectHelper;
        $region = $helper->getRegionFromCountyName($this->getCountryCode(), $this->_addressData->getCounty());

        $regionId = ($region) ? $region->getId() : 0;
        return $regionId;
    }

    public function getPostcode()
    {
        return $this->_addressData->getPostcode();
    }

    public function getCountryCode()
    {

        if (is_null($this->_countryCode)) {
            $helper = $this->customerconnectHelper;
            $this->_countryCode = $helper->getCountryCodeForDisplay($this->_addressData->getCountry(), $helper::ERP_TO_MAGENTO);
        }

        return $this->_countryCode;
    }

    public function getCountry()
    {
        try {
            $helper = $this->customerconnectHelper;

            return $helper->getCountryName($this->getCountryCode());
        } catch (\Exception $e) {
            return $this->_addressData->getCountry();
        }
    }

    public function getTelephoneNumber()
    {
        return $this->_addressData->getTelephoneNumber();
    }

    public function getFaxNumber()
    {
        return $this->_addressData->getFaxNumber();
    }

    public function getEmail()
    {
        return $this->_addressData->getEmailAddress();
    }
    
    public  function assignedFunctions()
    {
        $getConfig= $this->getConfig('dealerconnect_enabled_messages/DEID_request/grid_config');
        $oldData = unserialize($getConfig);    
        $indexVals = array();
        foreach ($oldData as $key=> $oldValues) {
           $indexVals[$oldValues['index']] = array( $oldValues['index'],$oldValues['header']);  
        }
        return $indexVals;        
    }


    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getAddressData()
    {
       return $this->_addressData; 
    }
    
    
    /**
     * @return \Magento\Framework\DataObject
     */
    public function getLocAddressData($postion=false)
    {
        if($postion) {
          $this->_addressData =  $this->_LocationAddressData;
          return $this->_addressData;
        }
    }    

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getOwnAddressData($postion=false)
    {
        if($postion) {
          $this->_addressData =  $this->_ownerAddressData;
          return $this->_addressData;
        }
   }


    /**
     * @return \Magento\Framework\DataObject
     */
    public function getSTAddressData($postion=false)
    {
        if($postion) {
          $this->_addressData =  $this->_soldAddressData;
           return $this->_addressData;        
        }
    }    

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $path
     * @return bool
     */
    public function getConfigFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return \Epicor\Customerconnect\Helper\Data
     */
    public function getCustomerconnectHelper()
    {
        return $this->customerconnectHelper;
    }

    /**
     * @return \Magento\Directory\Helper\Data
     */
    public function getDirectoryHelper()
    {
        return $this->directoryHelper;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return \Epicor\Common\Helper\Data
     */
    public function getCommonHelper()
    {
        return $this->commonHelper;
    }
    //M1 > M2 Translation End

}