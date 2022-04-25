<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CUAU - Customer Account Update
 *
 * Websales requesting update of customer account
 *
 * XML Data Support - Request
 * /brand/company                                           - supported
 * /brand/branch                                            - supported
 * /brand/warehouse                                         - supported
 * /brand/group                                             - supported
 * /accountNumber                                           - supported
 * /languageCode                                            - supported
 * /invoiceAddress                                          - supported
 * /invoiceAddress/oldInvoiceAddress                        - supported
 * /invoiceAddress/oldInvoiceAddress/addressCode            - supported
 * /invoiceAddress/oldInvoiceAddress/name                   - supported
 * /invoiceAddress/oldInvoiceAddress/address1               - supported
 * /invoiceAddress/oldInvoiceAddress/address2               - supported
 * /invoiceAddress/oldInvoiceAddress/address3               - supported
 * /invoiceAddress/oldInvoiceAddress/city                   - supported
 * /invoiceAddress/oldInvoiceAddress/county                 - supported
 * /invoiceAddress/oldInvoiceAddress/country                - supported
 * /invoiceAddress/oldInvoiceAddress/postCode               - supported
 * /invoiceAddress/oldInvoiceAddress/telephoneNumber        - supported
 * /invoiceAddress/oldInvoiceAddress/faxNumber              - supported
 * /invoiceAddress/name                                     - supported
 * /invoiceAddress/address1                                 - supported
 * /invoiceAddress/address2                                 - supported
 * /invoiceAddress/address3                                 - supported
 * /invoiceAddress/city                                     - supported
 * /invoiceAddress/county                                   - supported
 * /invoiceAddress/country                                  - supported
 * /invoiceAddress/postCode                                 - supported
 * /invoiceAddress/telephoneNumber                          - supported
 * /deliveryAddress/                                        - supported
 * /deliveryAddress/oldDeliveryAddress/                     - supported
 * /deliveryAddress/oldDeliveryAddress/addressCode          - supported
 * /deliveryAddress/oldDeliveryAddress/name                 - supported
 * /deliveryAddress/oldDeliveryAddress/address1             - supported
 * /deliveryAddress/oldDeliveryAddress/address2             - supported
 * /deliveryAddress/oldDeliveryAddress/address3             - supported
 * /deliveryAddress/oldDeliveryAddress/city                 - supported
 * /deliveryAddress/oldDeliveryAddress/county               - supported
 * /deliveryAddress/oldDeliveryAddress/country              - supported
 * /deliveryAddress/oldDeliveryAddress/postCode             - supported
 * /deliveryAddress/oldDeliveryAddress/telephoneNumber      - supported
 * /deliveryAddress/oldDeliveryAddress/faxNumber            - supported
 * /deliveryAddress/name                                    - supported
 * /deliveryAddress/address1                                - supported
 * /deliveryAddress/address2                                - supported
 * /deliveryAddress/address3                                - supported
 * /deliveryAddress/city                                    - supported
 * /deliveryAddress/county                                  - supported
 * /deliveryAddress/country                                 - supported
 * /deliveryAddress/postCode                                - supported
 * /deliveryAddress/telephoneNumber                         - supported
 * /deliveryAddress/faxNumber                               - supported
 * /contacts                                                - supported
 * /contacts/contact                                        - supported
 * /contacts/contact/oldContact                             - supported
 * /contacts/contact/oldContact/contactCode                 - supported
 * /contacts/contact/oldContact/name                        - supported
 * /contacts/contact/oldContact/function                    - supported
 * /contacts/contact/oldContact/telephoneNumber             - supported
 * /contacts/contact/oldContact/faxNumber                   - supported
 * /contacts/contact/oldContact/faxNumber                   - supported
 * /contacts/contact/oldContact/emailAddress                - supported
 * /contacts/contact/oldContact/loginId                     - supported
 *
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cuau extends \Epicor\Customerconnect\Model\Message\Request 
{

    protected $_contacts = array();
    protected $_invoiceAddress = array();
    protected $_deliveryAddresses = array();
    protected $_action;
    protected $_deliveryAddressNumber = 1;
    protected $_contactNumber = 1;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory
     */
    protected $commResourceCustomerErpaccountAddressCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;
    
    /**
     * @var \Epicor\Comm\Helper\MessagingFactory
     */
    protected $commMessagingHelper;
    
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;
    
    public function __construct(
        \Epicor\Comm\Model\Context $context, 
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper, 
        \Magento\Framework\Locale\ResolverInterface $localeResolver, 
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper, 
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory, 
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\CollectionFactory $commResourceCustomerErpaccountAddressCollectionFactory, 
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, 
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, 
        array $data = []) 
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->commResourceCustomerErpaccountAddressCollectionFactory = $commResourceCustomerErpaccountAddressCollectionFactory;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->commonHelper = $commonHelper;
        $this->commMessagingHelper = $context->getCommMessagingHelper();
        $this->directoryCountryFactory = $directoryCountryFactory;

        parent::__construct($context, $customerconnectMessagingHelper, $localeResolver, $resource, $resourceCollection, $data);

        $this->setMessageType('CUAU');
        $this->setConfigBase('customerconnect_enabled_messages/CUAU_request/');
        $this->setResultsPath('status');
    }

    
    public function buildRequest() 
    {
        $message['accountNumber'] = $this->getAccountNumber();
        $message['languageCode'] = $this->getLanguageCode();

        if (!empty($this->_invoiceAddress)) {
            $message['invoiceAddress'] = $this->_invoiceAddress;
        }

        $deliveryAddressTag = in_array($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), array('e10')) ? 'deliveryAddresss' : 'deliveryAddresses';
        if (!empty($this->_deliveryAddresses)) {
            $message[$deliveryAddressTag]['deliveryAddress'] = $this->_deliveryAddresses;
        } else {
            $message[$deliveryAddressTag] = '';    // if no data send empty repeating group tag 
        }

        if (!empty($this->_contacts)) {
            $message['contacts']['contact'] = $this->_contacts;
        }

        $data = $this->getMessageTemplate();
        $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], $message);

        $this->setOutXml($data);

        return true;
    }

    public function addInvoiceAddress($address, $oldAddress) 
    {
        $helper = $this->customerconnectHelper;
        if (isset($address['county_id'])) {
            $region = $this->directoryRegionFactory->create()->load($address['county_id']);
            /* @var $region Mage_Directory_Model_Region */
            $address['county'] = $helper->getRegionNameOrCode($region->getCountryId(), $region->getCode());
        }
        $this->_invoiceAddress = array(
            '_attributes' => array(
                'action' => 'U'
            ),
            'oldInvoiceAddress' => array(
                'addressCode' => $address['address_code'],
                'name' => $oldAddress['name'],
                'address1' => $oldAddress['address1'],
                'address2' => array_key_exists('address2', $oldAddress) ? $oldAddress['address2'] : null,
                'address3' => array_key_exists('address3', $oldAddress) ? $oldAddress['address3'] : null,
                'city' => $oldAddress['city'],
                'county' => $oldAddress['county'],
                'country' => $helper->getCountryCodeMapping($oldAddress['country']),
                'postcode' => $oldAddress['postcode'],
                'emailAddress' => array_key_exists('email', $oldAddress) ? $oldAddress['email'] : null,
                'telephoneNumber' => array_key_exists('telephone', $oldAddress) ? $oldAddress['telephone'] : null,
                'mobileNumber' => array_key_exists('mobile_number', $oldAddress) ? $oldAddress['mobile_number'] : null,
                'faxNumber' => array_key_exists('fax', $oldAddress) ? $oldAddress['fax'] : null
            ),
            'name' => $address['name'],
            'address1' => $address['address1'],
            'address2' => array_key_exists('address2', $address) ? $address['address2'] : null,
            'address3' => array_key_exists('address3', $address) ? $address['address3'] : null,
            'city' => $address['city'],
            'county' => $address['county'],
            'country' => $helper->getCountryCodeMapping($address['country']),
            'postcode' => $address['postcode'],
            'emailAddress' => array_key_exists('email', $address) ? $address['email'] : null,
            'telephoneNumber' => array_key_exists('telephone', $address) ? $address['telephone'] : null,
            'mobileNumber' => array_key_exists('mobile_number', $address) ? $address['mobile_number'] : null,
            'faxNumber' => array_key_exists('fax', $address) ? $address['fax'] : null,
        );

        return $this;
    }

    public function addDeliveryAddress($action, $address, $oldAddress = null) 
    {
        $helper = $this->customerconnectHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */

        if (isset($address['county_id'])) {
            $region = $this->directoryRegionFactory->create()->load($address['county_id']);
            /* @var $region Mage_Directory_Model_Region */
            $address['county'] = $helper->getRegionNameOrCode($region->getCountryId(), $region->getCode());
        }

        $deliveryAddress = array(
            '_attributes' => array(
                'action' => $action,
                'number' => $this->_deliveryAddressNumber
            ),
            'addressCode' => (isset($oldAddress['address_code'])) ? $oldAddress['address_code'] : $address['address_code'],
            'name' => $address['name'],
            'address1' => $address['address1'],
            'address2' => array_key_exists('address2', $address) ? $address['address2'] : null,
            'address3' => array_key_exists('address3', $address) ? $address['address3'] : null,
            'city' => $address['city'],
            'county' => $address['county'],
            'country' => $helper->getCountryCodeMapping($address['country']),
            'postcode' => $address['postcode'],
            'emailAddress' => array_key_exists('email', $address) ? $address['email'] : null,
            'telephoneNumber' => array_key_exists('telephone', $address) ? $address['telephone'] : null,
            'mobileNumber' => array_key_exists('mobile_number', $address) ? $address['mobile_number'] : null,
            'faxNumber' => array_key_exists('fax', $address) ? $address['fax'] : null,
            'oldDeliveryAddress' => array()
        );

        if ($oldAddress) {
            $deliveryAddress['oldDeliveryAddress'] = array(
                'addressCode' => $address['address_code'],
                'name' => $oldAddress['name'],
                'address1' => $oldAddress['address1'],
                'address2' => array_key_exists('address2', $oldAddress) ? $oldAddress['address2'] : null,
                'address3' => array_key_exists('address3', $oldAddress) ? $oldAddress['address3'] : null,
                'city' => $oldAddress['city'],
                'county' => $oldAddress['county'],
                'country' => $helper->getCountryCodeMapping($oldAddress['country']),
                'postcode' => $oldAddress['postcode'],
                'emailAddress' => array_key_exists('email', $oldAddress) ? $oldAddress['email'] : null,
                'mobileNumber' => array_key_exists('mobile_number', $oldAddress) ? $oldAddress['mobile_number'] : null,
                'telephoneNumber' => array_key_exists('telephone', $oldAddress) ? $oldAddress['telephone'] : null,
                'faxNumber' => array_key_exists('fax', $oldAddress) ? $oldAddress['fax'] : null
            );
        }

        $this->_deliveryAddresses[] = $deliveryAddress;
        $this->_deliveryAddressNumber++;

        return $this;
    }

    public function deleteDeliveryAddress($address) 
    {
        $helper = $this->customerconnectHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */

        if (isset($address['county_id'])) {
            $region = $this->directoryRegionFactory->create()->load($address['county_id']);
            /* @var $region Mage_Directory_Model_Region */
            // temporarily removed - might note be required    
            //    $address['county'] = $helper->getRegionNameOrCode($region->getCountryId(), $region->getCode()); 
            //      $address['county'] = $region->getCode();
        }

        $deliveryAddress = array(
            '_attributes' => array(
                'action' => 'D',
                'number' => $this->_deliveryAddressNumber
            ),
            'oldDeliveryAddress' => array(
                'addressCode' => $address['address_code'],
                'name' => $address['name'],
                'address1' => $address['address1'],
                'address2' => $address['address2'] ? $address['address2'] : null,
                'address3' => $address['address3'] ? $address['address3'] : null,
                'city' => $address['city'],
                'county' => $address['county'],
                'country' => $helper->getCountryCodeMapping($address['country']),
                'postcode' => $address['postcode'],
                'emailAddress' => array_key_exists('email', $address) ? $address['email'] : null,
                'telephoneNumber' => array_key_exists('telephone', $address) ? $address['telephone'] : null,
                'mobileNumber' => array_key_exists('mobile_number', $address) ? $address['mobile_number'] : null,
                'faxNumber' => array_key_exists('fax', $address) ? $address['fax'] : null,
            )
        );

        $this->_deliveryAddressNumber++;

        $this->_deliveryAddresses[] = $deliveryAddress;
        return $this;
    }

    public function addContact($action, $newContact, $oldContact) 
    {
        $contact = array(
            '_attributes' => array(
                'action' => $action,
                'number' => $this->_contactNumber
            ),
            'name' => $newContact['name'],
            'function' => $newContact['function'],
            'telephoneNumber' => $newContact['telephone_number'],
            'faxNumber' => $newContact['fax_number'],
            'emailAddress' => $newContact['email_address'],
            'loginId' => $newContact['login_id'],
            'oldContact' => array()
        );

        if ($oldContact) {
            $contact['oldContact'] = array(
                'contactCode' => $oldContact['contact_code'],
                'name' => $oldContact['name'],
                'function' => $oldContact['function'],
                'telephoneNumber' => $oldContact['telephone_number'],
                'faxNumber' => $oldContact['fax_number'],
                'emailAddress' => $oldContact['email_address'],
                'loginId' => $oldContact['login_id']
            );
        }

        $this->_contactNumber++;
        $this->_contacts[] = $contact;

        return $this;
    }

    public function deleteContact($oldContact) 
    {
        $contact = array(
            '_attributes' => array(
                'action' => 'D',
                'number' => $this->_contactNumber,
            ),
            'oldContact' => array(
                'contactCode' => $oldContact['contact_code'],
                'name' => $oldContact['name'],
                'function' => $oldContact['function'],
                'telephoneNumber' => $oldContact['telephone_number'],
                'faxNumber' => $oldContact['fax_number'],
                'emailAddress' => $oldContact['email_address'],
                'loginId' => $oldContact['login_id']
            )
        );

        $this->_contactNumber++;
        $this->_contacts[] = $contact;

        return $this;
    }

    public function sendMessage(\Zend_Http_Client $connection = null) 
    {
        $result = parent::sendMessage($connection);
        $addressType = $this->getAddressType() . 'Address';
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */
        $erpAccountInfo = $commHelper->getErpAccountInfo();
        /* @var $erpAccountInfo Epicor_Comm_Model_Customer_Erpaccount */
        $erpCustomerGroupCode = $erpAccountInfo->getErpCode();
        if ($addressType == 'deliveryAddress') {
            $address = $this->_deliveryAddresses[0];
            $isDelivery = true;
        } else {
            $address = $this->_invoiceAddress;
            $isDelivery = false;
        }
        if (!$result && $this->getAction()) {
            switch ($this->getAction()) {
                case 'delete':
                    $this->deleteAddress($address, $erpCustomerGroupCode);
                    return true;
                    break;

                case 'update':
                    $this->updateAddress($address, true, $isDelivery, $erpCustomerGroupCode);
                    return true;
                    break;
            }
        }

        return $result;
    }

    public function processResponse() 
    {
        $addressType = $this->getAddressType() . 'Address';
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */
        $erpAccountInfo = $commHelper->getErpAccountInfo();
        /* @var $erpAccountInfo Epicor_Comm_Model_Customer_Erpaccount */
        $erpCustomerGroupCode = $erpAccountInfo->getErpCode();
        
        if ($addressType == 'deliveryAddress') {
            $address = $this->_deliveryAddresses[0];
            $isDelivery = true;
        } else {
            $address = $this->_invoiceAddress;
            $isDelivery = false;
        }
        if (parent::processResponse()) {
            $customer = $this->getResponse()->getCustomer();
            if ($customer && $this->getAction()) {
                switch ($this->getAction()) {
                    case 'delete':
                        $this->deleteAddress($address, $erpCustomerGroupCode);
                        break;

                    case 'add':
                        $this->createAddress($address, $erpCustomerGroupCode);
                        break;

                    case 'update':
                        $this->updateAddress($address, false, $isDelivery, $erpCustomerGroupCode);
                        break;
                }
            }
            return true;
        } else {
            if ($this->getAction()) {
                switch ($this->getAction()) {
                    case 'delete':
                        $this->deleteAddress($address, $erpCustomerGroupCode);
                        return true;
                        break;

                    case 'update':
                        $this->updateAddress($address, true, $isDelivery, $erpCustomerGroupCode);
                        return true;
                        break;
                }
            }
            return false;
        }
    }

    private function updateAddress($address, $error = false, $isDelivery = true, $erpCustomerGroupCode) 
    {
        try {
            $helper = $this->commMessagingHelper->create();
            /* @var $helper Epicor_Comm_Helper_Messaging */
            $filteredAddresses = [];
            if ($isDelivery) {
                $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()->addFieldToFilter('erp_customer_group_code', array('eq' => $erpCustomerGroupCode))->addFieldToFilter('erp_code', array('eq' => $address['addressCode']))
                        ->getFirstItem();
            } else {
                $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()->addFieldToFilter('erp_customer_group_code', array('eq' => $erpCustomerGroupCode))->addFieldToFilter('erp_code', array('eq' => $address['oldInvoiceAddress']['addressCode']))
                        ->getFirstItem();
            }
            if (!$collection->isObjectNew()) {
                if (!$error) {
                    $xmlHelper = $this->commonHelper;
                    /* @var $helper Epicor_Common_Helper_Xml */
                    $e10 = in_array($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), array('e10'));
                    if ($isDelivery) {
                        $deliveryAddresses = $e10 ? $this->getResponse()->getCustomer()->getDeliveryAddresss() : $this->getResponse()->getCustomer()->getDeliveryAddresses();
                        $deliveryAddresses = $xmlHelper->varienToArray($deliveryAddresses);
                        if (isset($deliveryAddresses['delivery_address']['address_code'])) {
                            if($address['addressCode'] === $deliveryAddresses['delivery_address']['address_code']){
                                $filteredAddresses[0] = $deliveryAddresses['delivery_address']; 
                            }
                        } else {
                            $filteredAddresses = array_values(array_filter($deliveryAddresses['delivery_address'], function($arrayValue) use($address) {
                                        return $arrayValue['address_code'] === $address['addressCode'];
                                    }));
                        }
                        $update = !empty($filteredAddresses) ? $filteredAddresses[0] : $address;
                    } else {
                        $invoiceAddress = $this->getResponse()->getCustomer()->getInvoiceAddress();
                        $invoiceAddress = $xmlHelper->varienToArray($invoiceAddress);
                        $update = $invoiceAddress['address_code'] === $address['oldInvoiceAddress']['addressCode'] ? $invoiceAddress : $address;
                    }
                } else {
                    $update = $address;
                }
                $countryModel = $this->directoryCountryFactory->create()->loadByCode($helper->getCountryCodeMapping($update['country'], $helper::ERP_TO_MAGENTO));
                $region = $this->directoryRegionFactory->create()->loadByCode($update['county'], $countryModel->getId());
                if(empty($region) || $region->isObjectNew()){
                    $region = $this->directoryRegionFactory->create()->loadByName($update['county'], $countryModel->getId());
                }
                $collection->setName($update['name'])
                        //M1 > M2 Translation Begin (Rule 9)
                        /* ->setAddress1($address->getAddress1())
                          ->setAddress2($address->getAddress2())
                          ->setAddress3($address->getAddress3()) */
                        ->setData('address1', $update['address1'])
                        ->setData('address2', $update['address2'])
                        ->setData('address3', $update['address3'])
                        //M1 > M2 Translation End
                        ->setCity($update['city'])
                        ->setCounty($region->getName() ?: $update['county'])
                        ->setCountry($helper->getCountryCodeMapping($update['country'], $helper::ERP_TO_MAGENTO))
                        ->setPostcode($update['postcode'])
                        ->setPhone(isset($update['telephoneNumber']) ? $update['telephoneNumber'] : $update['telephone_number'])
//                        ->setMobileNumber($update['mobileNumber'] ?: null)
                        ->setFax(isset($update['faxNumber']) ? $update['faxNumber'] : $update['fax_number'])
                        ->setEmail(isset($update['emailAddress']) ? $update['emailAddress'] : null);
                if ($region) {
                    $collection->setCountyCode($region->getCode() ?: '');
                }
                $collection->save();
            }
        } catch (\Exception $ex) {
            $this->_errorMsg = __('Error updating ' . $this->getAddressType() . ' address ');
        }
    }

    private function deleteAddress($address, $erpCustomerGroupCode) 
    {
        try {
            $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()->addFieldToFilter('erp_customer_group_code', array('eq' => $erpCustomerGroupCode))->addFieldToFilter('erp_code', array('eq' => $address['oldDeliveryAddress']['addressCode']))
                    ->getFirstItem();
            if (!$collection->isObjectNew()) {
                $collection->delete();
            }
        } catch (\Exception $ex) {
            $this->_errorMsg = __('Error deleting ' . $this->getAddressType() . ' address ');
        }
    }

    private function createAddress($address, $erpCustomerGroupCode) 
    {
        try {
            $xmlHelper = $this->commonHelper;
            /* @var $helper Epicor_Common_Helper_Xml */
            $helper = $this->commMessagingHelper->create();
            /* @var $helper Epicor_Comm_Helper_Messaging */
            $e10 = in_array($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), array('e10'));
            $deliveryAddresses = $e10 ? $this->getResponse()->getCustomer()->getDeliveryAddresss() : $this->getResponse()->getCustomer()->getDeliveryAddresses();
            $multipleAddressesSupplied = is_object($deliveryAddresses['delivery_address'][0]) ? true :false;
            $deliveryAddresses = $xmlHelper->varienToArray($deliveryAddresses);
            if($multipleAddressesSupplied){
                //loop through all the delivery addresses
                foreach ($deliveryAddresses['delivery_address'] as $deliveryAddress) {
                    $this->saveNewAddress($deliveryAddress, $erpCustomerGroupCode);
                }
            }else{
                //save the only delivery address supplied
                $this->saveNewAddress($deliveryAddresses['delivery_address'], $erpCustomerGroupCode);
            }
        } catch (\Exception $ex) {
            $this->_errorMsg = __('Error creating ' . $this->getAddressType() . ' address ');
        }
    }
    private function saveNewAddress($deliveryAddress, $erpCustomerGroupCode)
    {
        $filteredAddresses = null;
        $helper = $this->commMessagingHelper->create();
        /* @var $helper Epicor_Comm_Helper_Messaging */
        if (isset($deliveryAddress['address_code'])) {
            $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()
                ->addFieldToFilter('erp_customer_group_code', array('eq' => $erpCustomerGroupCode))
                ->addFieldToFilter('erp_code', array('eq' => $deliveryAddress['address_code']))
                ->getFirstItem();
            if (!$collection->getId()) {
                $filteredAddresses = $deliveryAddress;
            }
        } else {
            $filteredAddresses = array_values(array_filter($deliveryAddress,
                        function ($arrayValue) use ($erpCustomerGroupCode) {
                        $collection = $this->commResourceCustomerErpaccountAddressCollectionFactory->create()
                            ->addFieldToFilter('erp_customer_group_code', array('eq' => $erpCustomerGroupCode))
                            ->addFieldToFilter('erp_code', array('eq' => $arrayValue['address_code']))
                            ->getFirstItem();
                        if (!$collection->getId()) {
                            return true;
                        } else {
                            return false;
                        }
            }));
        }

        if (!empty($filteredAddresses)) {

                $newAddress = $this->commCustomerErpaccountAddressFactory->create();
                $countryModel = $this->directoryCountryFactory->create()
                                ->loadByCode($helper->getCountryCodeMapping($filteredAddresses['country'],
                                    $helper::ERP_TO_MAGENTO));
                $region = $this->directoryRegionFactory->create()->
                            loadByCode($filteredAddresses['county'], $countryModel->getId());
                if(empty($region) || $region->isObjectNew()){
                    $region = $this->directoryRegionFactory->create()
                              ->loadByName($filteredAddresses['county'], $countryModel->getId());
                }
                $newAddress->setName($filteredAddresses['name'])
                    //M1 > M2 Translation Begin (Rule 9)
                    /* ->setAddress1($address->getAddress1())
                      ->setAddress2($address->getAddress2())
                      ->setAddress3($address->getAddress3()) */
                    ->setData('address1', $filteredAddresses['address1'])
                    ->setData('address2', $filteredAddresses['address2'])
                    ->setData('address3', $filteredAddresses['address3'])
                    //M1 > M2 Translation End
                    ->setCity($filteredAddresses['city'])
                    ->setCounty($region->getName() ?: $filteredAddresses['county'])
                    ->setCountry($helper->getCountryCodeMapping($filteredAddresses['country'],
                                 $helper::ERP_TO_MAGENTO))
                    ->setPostcode($filteredAddresses['postcode'])
                    ->setPhone($filteredAddresses['telephone_number'])
                    ->setFax($filteredAddresses['fax_number'])
                    ->setErpCode($filteredAddresses['address_code'])
                    ->setIsDelivery(true)
                    ->setErpCustomerGroupCode($erpCustomerGroupCode);
            if ($region) {
                $newAddress->setCountyCode($region->getCode() ?: '');
            }
            $newAddress->save();
        }
    }
}
