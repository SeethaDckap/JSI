<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Details;


class UpdateAddress extends  \Magento\Framework\View\Element\Template
{
    
        /**
         * @var \Magento\Framework\Registry
         */
        protected $registry;    
        
        protected $_validationLocationFields = array('street1', 'city', 'country_id', 'postcode', 'telephone');

         /**
          * @var \Epicor\BranchPickup\Helper\Data
          */
         protected $branchPickupHelper;


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
         
         
         protected $request;
         
         private $dataObjectFactory;
         
         /**
         * @var \Epicor\Comm\Helper\Messaging
         */
         protected $commMessagingHelper;         

        /**
         * @var \Magento\Framework\App\Config\ScopeConfigInterface
         */
        protected $scopeConfig;        
        
        protected $warrantyCollectionFactory;
        
        
        protected $dealerconnectHelper;
        
        /**
        * @var \Magento\Customer\Model\Session
        */
        protected $customerSession;
        
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
            \Magento\Customer\Model\Session $customerSession,
            array $data = []
        )
        {
            $this->countryCollectionFactory = $collectionFactory;
            $this->directoryCountryFactory = $directoryCountryFactory;
            $this->directoryRegionFactory = $directoryRegionFactory;  
            $this->scopeConfig =$context->getScopeConfig();
            $this->registry = $registry;
            $this->request = $request;
            $this->warrantyCollectionFactory = $warrantyCollectionFactory;
            $this->dataObjectFactory = $dataObjectFactory;
            $this->commMessagingHelper = $commMessagingHelper;
            $this->dealerconnectHelper = $dealerconnectHelper;
            $this->customerSession = $customerSession;
            $this->getLocationAddress();        
            $this->getSoldToAddress();        
            $this->getAddress();        
            $this->country = $country;
            parent::__construct(
                $context,
                $data
            );
        }     
        
        
        public  function getFormSaveUrl()
        {
            return $this->getUrl('dealerconnect/inventory/addlocation');
        }
        
        
        public function split_name($name) {
            $name = trim($name);
            $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
            $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
            return array($first_name, $last_name);
        }          
        
        
        public  function getBasicJsonDetails() {
           $basicjson = $this->request->getParam('basicjson'); 
           return $basicjson;
        }
        
        public function getWarranyInformations() {
            $basicjson = $this->request->getParam('basicjson'); 
            $jsonDecode = json_decode($basicjson,true);
            $warranty = array();
            $date=date_create($jsonDecode['warranty_expiration_date']);
            $endDate =  date_format($date,"m/d/Y"); 
            $date1=date_create($jsonDecode['warranty_start_date']);
            $startDate =  date_format($date1,"m/d/Y");             
            $warranty['code']= $jsonDecode['warranty_code'];
            $warranty['enddate']= ($jsonDecode['warranty_expiration_date']) ? $endDate : "";
            $warranty['startdate']= ($jsonDecode['warranty_start_date']) ? $startDate : "";
            $warranty['comment']= $jsonDecode['warranty_comment'];
            return $warranty;
        }




        public  function getActionMode() {
           $actionMode = $this->request->getParam('mode'); 
           return $actionMode;
        }     
        
        public function getConfigFlag($path)
        {
            return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
        }        


        public function getLocationAddress()
	{
            $dataObject = $this->dataObjectFactory->create();
            return $dataObject;
//             $locationAddress = $this->request->getParam('locationAddress');
//             $jsonDecode = json_decode($locationAddress,true);
//             if($jsonDecode['country']) {
//                $helper = $this->commMessagingHelper;
//                /* @var $helper \Epicor\Comm\Helper\Messaging */                 
//                $jsonDecode['country'] = $helper->getCountryCodeMapping($jsonDecode['country'], $helper::ERP_TO_MAGENTO);
//             }
//             $actionMode = $this->getActionMode();
//             $isNew = false;
//             if($actionMode =="add") {
//                $isNew = true;
//             } 
//             
//             $countyCode = $jsonDecode['county'];
//             $regionId = '';
//             if (!empty($countyCode) && !empty($jsonDecode['country'])) {
//                $countryModel = $this->directoryCountryFactory->create()->loadByCode($jsonDecode['country']);
//                $countyCodes= $this->directoryRegionFactory->create()->loadByName($countyCode, $countryModel->getId());
//                if(!$countyCodes->getRegionId()) {
//                    $countyCodes= $this->directoryRegionFactory->create()->loadByCode($countyCode, $countryModel->getId());
//                }                
//                $jsonDecode['county'] = ($countyCodes->getRegionId()) ? $countyCodes->getRegionId(): $countyCode;
//            }   
//            if($isNew) {
//                $jsonDecode = array_fill_keys(array_keys($jsonDecode), '');
//            }         
//             $dataObject->addData($jsonDecode);
//             return $dataObject;
	}
        
        
	public function getSoldToAddress()
	{
             $locationAddress = $this->request->getParam('soldtojson');
             $jsonDecode = json_decode($locationAddress,true);
             $dataObject = $this->dataObjectFactory->create();
             if($jsonDecode['country']) {
                $helper = $this->commMessagingHelper;
                /* @var $helper \Epicor\Comm\Helper\Messaging */                 
                $jsonDecode['country'] = $helper->getCountryCodeMapping($jsonDecode['country'], $helper::ERP_TO_MAGENTO);
             }
             $actionMode = $this->getActionMode();
             $isNew = false;
             if($actionMode =="add") {
                $isNew = true;
             } 
             
             $countyCode = $jsonDecode['county'];
             $regionId = '';
             if (!empty($countyCode) && !empty($jsonDecode['country'])) {
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($jsonDecode['country']);
                $countyCodes= $this->directoryRegionFactory->create()->loadByName($countyCode, $countryModel->getId());
                if(!$countyCodes->getRegionId()) {
                    $countyCodes= $this->directoryRegionFactory->create()->loadByCode($countyCode, $countryModel->getId());
                }                
                $jsonDecode['county'] = ($countyCodes->getRegionId()) ? $countyCodes->getRegionId(): $countyCode;
            }   
            if($isNew) {
                $jsonDecode = array_fill_keys(array_keys($jsonDecode), '');
            }
             
             $dataObject->addData($jsonDecode);
             return $dataObject;
	} 
        
	public function getOwnerAddress()
	{
            $dataObject = $this->dataObjectFactory->create();
            return $dataObject;
//             $locationAddress = $this->request->getParam('ownerjson');
//             $jsonDecode = json_decode($locationAddress,true);
//             $dataObject = $this->dataObjectFactory->create();
//             if($jsonDecode['country']) {
//                $helper = $this->commMessagingHelper;
//                /* @var $helper \Epicor\Comm\Helper\Messaging */                 
//                $jsonDecode['country'] = $helper->getCountryCodeMapping($jsonDecode['country'], $helper::ERP_TO_MAGENTO);
//             }
//             $actionMode = $this->getActionMode();
//             $isNew = false;
//             if($actionMode =="add") {
//                $isNew = true;
//             } 
//             
//             $countyCode = $jsonDecode['county'];
//             $regionId = '';
//             if (!empty($countyCode) && !empty($jsonDecode['country'])) {
//                $countryModel = $this->directoryCountryFactory->create()->loadByCode($jsonDecode['country']);
//                $countyCodes= $this->directoryRegionFactory->create()->loadByName($countyCode, $countryModel->getId());
//                if(!$countyCodes->getRegionId()) {
//                    $countyCodes= $this->directoryRegionFactory->create()->loadByCode($countyCode, $countryModel->getId());
//                }
//                $jsonDecode['county'] = ($countyCodes->getRegionId()) ? $countyCodes->getRegionId(): $countyCode;
//            }   
//            if($isNew) {
//                $jsonDecode = array_fill_keys(array_keys($jsonDecode), '');
//            }             
//             $dataObject->addData($jsonDecode);
//             return $dataObject;
	}     
        
    //M1 > M2 Translation Begin (Rule p2-5.2)
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
    
    public  function getWarrantyDescription($code) {
       $collection = $this->warrantyCollectionFactory->create();
       $collection->addFieldToFilter('status', 'yes');
       $collection->addFieldToFilter('code',$code);
       $countItems = $collection->getFirstItem();
       if($countItems) {
           $description = ($countItems->getDescription()) ? $countItems->getDescription() : $code;
       } else {
           $description = $code;
       }
       return $description;
    }      
    
    public function getLocationSelectBox($type) {
        
        $helper = $this->commMessagingHelper;
        $customer = $this->customerSession->getCustomer();
        if (!$customer->getId()) {
            $customer = $customer->load($this->customerSession->getId());
            $this->customerSession->setCustomer($customer);
        }
        $addresses = $customer->getCustomAddresses();
        foreach ($addresses as $address) {
            /* @var $address \Magento\Customer\Model\Address */
            $formatted = trim(ltrim(trim(str_replace($customer->getName() . ',', $address->getCompany() . ',', $address->format('oneline'))), ','));
            $options[] = array(
                'value' => $address->getId(),
                'label' => $formatted,
                'params' => array(
                    'data-iscustom' => $address->getIsCustom(),
                    'data-address' => htmlentities(json_encode(array(
                        'addressCode' => $address->getEccErpAddressCode(),
                        'name' => $helper->stripNonPrintableChars($address->getName()),
                        'contactName' => $helper->stripNonPrintableChars($address->getCompany()),
                        'address1' => $helper->stripNonPrintableChars($address->getStreet1()),
                        'address2' => $helper->stripNonPrintableChars($address->getStreet2()),
                        'address3' => $helper->stripNonPrintableChars($address->getStreet3()),
                        'city' => $helper->stripNonPrintableChars($address->getCity()),
                        'county' => $helper->stripNonPrintableChars($helper->getRegionNameOrCode($address->getCountry_id(), ($address->getRegionId() ? $address->getRegionId() : $address->getRegion()))),
                        'country' => $helper->getErpCountryCode($address->getCountry_id()),
                        'postcode' => $helper->stripNonPrintableChars($address->getPostcode()),
                        'telephoneNumber' => $helper->stripNonPrintableChars($address->getTelephone()),
                        'mobileNumber' => $helper->stripNonPrintableChars($address->getEccMobileNumber()),
                        'faxNumber' => $helper->stripNonPrintableChars($address->getFax()),
                    )))
                )
            );
        }
        array_push($options, array('value' => '', 'label' => 'New Address', 'params' => array()));

        $select = $this->getLayout()->createBlock('\Magento\Framework\View\Element\Html\Select')
                ->setName('delivery_address_id-'.$type)
                ->setId('delivery-address-select-'.$type)
                ->setClass('address-select')
                ->setValue('')
                ->setOptions($options);

        $html = $select->getHtml();

        return $html;
    }

}
