<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request;


/**
 * Request CUOS - Customer Order Search  
 * 
 * Websales requesting search for orders for account
 * 
 * XML Data Support - Request
 * /brand/company                                           - supported
 * /brand/branch                                            - supported
 * /brand/warehouse                                         - supported
 * /brand/group                                             - supported 
 * /results/maxResults                                      - supported
 * /results/rangeMin                                        - supported
 * /results/searches/search/criteria                        - supported
 * /results/searches/search/condition                       - supported
 * /results/searches/search/value                           - supported
 * /accountNumber                                           - supported 
 * /languageCode                                            - supported   
 * /currencies/currency/currencyCode                        - supported
 * 

 * 
 * 
 * @category   Epicor
 * @package    Epicor_DealersPortal
 * @author     Epicor Websales Team
 */
class Deiu extends \Epicor\Customerconnect\Model\Message\Request
{
    
    
    protected  $soldToAddressess = array();
    protected  $_ownerAddress = array();
    protected  $_locationAddress = array();


    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;

        parent::__construct($context, $customerconnectMessagingHelper, $localeResolver, $resource, $resourceCollection, $data);

        $this->setMessageType('DEIU');
        $this->setLicenseType('Dealer_Portal');
        $this->setConfigBase('dealerconnect_enabled_messages/DEIU_request/');
        $this->setResultsPath('status');
    }    


    public function buildRequest()
    {
        $message['accountNumber'] = $this->getAccountNumber();
        $message['languageCode'] = $this->getLanguageCode();
        $message['locationInventory'] = $this->_basicInformation;
        $message['locationInventory']['locationAddress'] = $this->_locationAddress;
        $message['locationInventory']['ownerAddress'] = $this->_ownerAddress;
        if(isset($this->soldToAddressess)) {
            $message['locationInventory']['soldToAddress'] = $this->soldToAddressess;
        }
        $data = $this->getMessageTemplate();
        $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], $message);
        $this->setOutXml($data);
        return true;
    }
    
    
    public function addBasicInformation($details)
    {  
        $basicDetails = $details;
        $helper = $this->customerconnectHelper;
        $mode = ($basicDetails['actionMode'] =="update")  ? "U": "A";
        $this->_basicInformation = array(
            '_attributes' => array(
                'action' =>$mode
            ),
            'locationNumber' => $basicDetails['location_number'],
            'identificationNumber' =>($mode =="A" && (!isset($basicDetails['identification_number']) || $basicDetails['identification_number'] == "")) ? rand():$basicDetails['identification_number'],
            'serialNumber' => $basicDetails['serial_number'],
            'productCode' => $basicDetails['product_code'],
            'description' => $basicDetails['description'],
            'tranComment' => $basicDetails['tranComment'],
            'listing' => $basicDetails['listing'],
            'listingDate' => $basicDetails['listing_date'],
            'effectiveDate' => date("Y-m-d"),
            'warrantyCode' => $basicDetails['warranty_code'],
            'warrantyComment' => $basicDetails['warranty_comment'],
            'warrantyExpirationDate' => $basicDetails['warranty_expiration_date'],
            'warrantyStartDate' => $basicDetails['warranty_start_date'],
        );
        
        if($mode =="A") {
           unset($this->_basicInformation['locationNumber']);
        }
        if(!$basicDetails['warranty_code']) {
            unset($this->_basicInformation['warrantyCode']);
            unset($this->_basicInformation['warrantyComment']);
            unset($this->_basicInformation['warrantyExpirationDate']);
            unset($this->_basicInformation['warrantyStartDate']);
        }
        return $this;
    }    
    
    
    public function addlocationAddress($address)
    {  
        $addressDetails = $address['locationAddress'];
        
        $helper = $this->customerconnectHelper;
        if (isset($addressDetails['county_id'])) {
            $region = $this->directoryRegionFactory->create()->load($addressDetails['county_id']);
            $addressDetails['county'] = $helper->getRegionNameOrCode($region->getCountryId(), $region->getCode());
        }
        
        if(isset($addressDetails['address_code']) && $addressDetails['address_code']) {
            $mode = "N";
        } else {
            $mode = "Y"; 
        }
        $addressDetails['account_number'] = isset($addressDetails['account_number']) ? $addressDetails['account_number'] : "";
        $this->_locationAddress = array(
           // '_attributes' => array(
           //     'useOTS' => ($addressDetails['actionMode'] =="update") ? "Y" : "N"
           // ),
            '_attributes' => array(
                'useOTS' => $mode
            ),            
            
            'accountNumber' => $mode == "Y" ? '0' : $addressDetails['account_number'],
            'addressCode' => isset($addressDetails['address_code']) ? $addressDetails['address_code'] : "",
            'name' => isset($addressDetails['company']) ? $addressDetails['company'] : "",
            'contactName' => $addressDetails['firstname']." ".$addressDetails['lastname'],
            'address1' => $addressDetails['street'][0],
            'address2' => $addressDetails['street'][1],
            'address3' => array_key_exists('2', $addressDetails['street']) ? $addressDetails['street'][2] : null,
            'city' => $addressDetails['city'],
            'county' => (isset($addressDetails['county']))? $addressDetails['county'] : $addressDetails['region'],
            'country' => $helper->getCountryCodeMapping($addressDetails['country']),
            'postcode' => $addressDetails['postcode'],
            'emailAddress' => array_key_exists('emailaddress', $addressDetails) ? $addressDetails['emailaddress'] : null,
            'telephoneNumber' => array_key_exists('telephone', $addressDetails) ? $addressDetails['telephone'] : null,
            'faxNumber' => array_key_exists('fax', $addressDetails) ? $addressDetails['fax'] : null,
        );
        
        return $this;
    }
   
    public function addownerAddress($address)
    {  
        $addressDetails = $address;
        $helper = $this->customerconnectHelper;
        if (isset($addressDetails['county_id'])) {
            $region = $this->directoryRegionFactory->create()->load($addressDetails['county_id']);
            $addressDetails['county'] = $helper->getRegionNameOrCode($region->getCountryId(), $region->getCode());
        }
        if(isset($addressDetails['address_code']) && $addressDetails['address_code']) {
            $mode = "N";
        } else {
            $mode = "Y"; 
        }       
        $addressDetails['account_number'] = isset($addressDetails['account_number']) ? $addressDetails['account_number'] : "";
        $this->_ownerAddress = array(
            '_attributes' => array(
                'useOTS' =>  $mode
            ),

            'accountNumber' => $mode == "Y" ? '0' : $addressDetails['account_number'],
            'addressCode' => isset($addressDetails['address_code']) ? $addressDetails['address_code'] : "",
            'name' => isset($addressDetails['company']) ? $addressDetails['company'] : "",
            'contactName' => $addressDetails['firstname']." ".$addressDetails['lastname'],
            'address1' => $addressDetails['street'][0],
            'address2' => $addressDetails['street'][1],
            'address3' => array_key_exists('2', $addressDetails['street']) ? $addressDetails['street'][2] : null,
            'city' => $addressDetails['city'],
            'county' => (isset($addressDetails['county']))? $addressDetails['county'] : $addressDetails['region'],
            'country' => $helper->getCountryCodeMapping($addressDetails['country']),
            'postcode' => $addressDetails['postcode'],
            'emailAddress' => array_key_exists('emailaddress', $addressDetails) ? $addressDetails['emailaddress'] : null,
            'telephoneNumber' => array_key_exists('telephone', $addressDetails) ? $addressDetails['telephone'] : null,
            'faxNumber' => array_key_exists('fax', $addressDetails) ? $addressDetails['fax'] : null,
        );
        
        return $this;
    }    
    
    
    public function addsoldToAddress($address)
    {  
        $addressDetails = $address;
        $helper = $this->customerconnectHelper;
        if (isset($addressDetails['county_id'])) {
            $region = $this->directoryRegionFactory->create()->load($addressDetails['county_id']);
            $addressDetails['county'] = $helper->getRegionNameOrCode($region->getCountryId(), $region->getCode());
        }
        
        if(isset($addressDetails['address_code']) && $addressDetails['address_code']) {
            $mode = "N";
        } else {
            $mode = "Y"; 
        }
        $this->soldToAddressess = array(
            '_attributes' => array(
                'useOTS' =>  $mode
            ),
            'accountNumber' => isset($addressDetails['account_number']) ? $addressDetails['account_number'] : "",
            'addressCode' => isset($addressDetails['address_code']) ? $addressDetails['address_code'] : "",
            'name' => isset($addressDetails['company']) ? $addressDetails['company'] : "",
            'contactName' => $addressDetails['firstname']." ".$addressDetails['lastname'],
            'address1' => $addressDetails['street'][0],
            'address2' => $addressDetails['street'][1],
            'address3' => array_key_exists('2', $addressDetails['street']) ? $addressDetails['street'][2] : null,
            'city' => $addressDetails['city'],
            'county' => (isset($addressDetails['county']))? $addressDetails['county'] : $addressDetails['region'],
            'country' => $helper->getCountryCodeMapping($addressDetails['country']),
            'postcode' => $addressDetails['postcode'],
            'emailAddress' => array_key_exists('emailaddress', $addressDetails) ? $addressDetails['emailaddress'] : null,
            'telephoneNumber' => array_key_exists('telephone', $addressDetails) ? $addressDetails['telephone'] : null,
            'faxNumber' => array_key_exists('fax', $addressDetails) ? $addressDetails['fax'] : null,
        );

        return $this;
    }       
  

 
    
}