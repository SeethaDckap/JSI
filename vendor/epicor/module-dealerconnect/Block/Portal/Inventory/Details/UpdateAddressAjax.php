<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Portal\Inventory\Details;

class UpdateAddressAjax extends \Magento\Framework\View\Element\Template {

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
    /*
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    protected $_addressData;
    protected $tagName;

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
        \Epicor\Comm\Helper\Data $commHelper, 
        array $data = []
    ) {
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
        $this->customerSession = $customerSession;
        $this->_addressData = array();
        $this->country = $country;
        $this->commHelper = $commHelper;
        parent::__construct(
                $context, $data
        );
        $this->setTemplate('Epicor_Dealerconnect::epicor/dealerconnect/deid/updateaddressajax.phtml');
    }

    public function split_name($name) 
    {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));
        return array($first_name, $last_name);
    }

    public function getConfigFlag($path) 
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getLocationAddress() 
    {
        $locationAddress = $this->_addressData;

        $dataObject = $this->dataObjectFactory->create();
        if ($locationAddress['country']) {
            $helper = $this->commMessagingHelper;
            /* @var $helper \Epicor\Comm\Helper\Messaging */
            $locationAddress['country'] = $helper->getCountryCodeMapping($locationAddress['country'], $helper::ERP_TO_MAGENTO);
        }
        $countyCode = $locationAddress['county'];
        $regionId = '';
        if (!empty($countyCode) && !empty($locationAddress['country'])) {
            $countryModel = $this->directoryCountryFactory->create()->loadByCode($locationAddress['country']);
            $countyCodes = $this->directoryRegionFactory->create()->loadByName($countyCode, $countryModel->getId());
            if (!$countyCodes->getRegionId()) {
                $countyCodes = $this->directoryRegionFactory->create()->loadByCode($countyCode, $countryModel->getId());
            }
            $locationAddress['county'] = ($countyCodes->getRegionId()) ? $countyCodes->getRegionId() : $countyCode;
        }
        return $locationAddress;
    }

    public function getCountryCollection() 
    {
        return $this->countryCollectionFactory->create();
    }

    public function setAddressFromCustomerAddress($data) 
    {
    
        /* @var $data \Magento\Customer\Model\Address */
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        $this->_addressData = $this->dataObjectFactory->create(
                [
                    'data' => array(
                        'account_number' => $erpAccount->getAccountNumber(),
                        'name' => $data->getName(),
                        'company' => $data->getCompany(),
                        'address1' => $data->getStreet()[0],
                        'address2' => isset($data->getStreet()[1]) ? $data->getStreet()[1] : '',
                        'address3' => isset($data->getStreet()[2]) ? $data->getStreet()[2] : '',
                        'city' => $data->getCity(),
                        'county' => $data->getCounty() ?: $data->getRegionCode(),
                        'country' => $data->getCountry(),
                        'postcode' => $data->getPostcode(),
                        'email' => $data->getEccEmail(),
                        'telephone_number' => $data->getTelephone(),
                        'fax' => $data->getFax(),
                        'address_code' => $data->getEccErpAddressCode(),
                        'instructions' => $data->getEccInstructions()
                    )
                ]
        );

        return $this;
    }

    public function getTagName() 
    {
        return $this->request->getParam('addresstype');
    }

}
