<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Locationpicker
 *
 * @author Paul.Ketelle
 */
class Locationpicker extends \Magento\Framework\View\Element\Template
{

    protected $_locationHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    ) {
        $this->commLocationsHelper = $commLocationsHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Location Picker'));
    }

    /**
     * Get Location Helper
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getLocationHelper()
    {
        if (!$this->_locationHelper) {
            $this->_locationHelper = $this->commLocationsHelper;
        }
        return $this->_locationHelper;
    }

    public function isAllowed()
    {
        $stockVisibility = $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $locationHelper = $this->getLocationHelper();
        $enabled = $locationHelper->isLocationsEnabled() && $locationHelper->showLocationPicker();
        $locationsCount = count($this->getCustomerAllowedLocations());

        return $enabled && $locationsCount > 1 && $stockVisibility != 'all_source_locations';
    }

    /**
     * Get the customer from the session
     * 
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomer()
    {
        $session = $this->customerSession;
        /* @var $session Mage_Customer_Model_Session */
        $customer = $session->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */
        return $customer;
    }

    public function getReturnUrl()
    {
        $url = $this->_urlBuilder->getCurrentUrl();
        return $this->commHelper->getUrlEncoder()->encode($url);
    }

    public function getFormUrl()
    {
        return $this->getUrl('epicor_comm/locations/filter');
    }

    /**
     * Checks config to see if user can choose single or multiple locations
     * 
     * @return boolean
     */
    public function canChooseMultipleLocations()
    {
        return true;
    }

    /**
     * 
     * @param string $code
     * 
     * @return boolean
     */
    public function isLocationSelected($code)
    {
        return in_array($code, $this->getLocationHelper()->getCustomerDisplayLocationCodes());
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
     * Render Location Picker Block
     *
     * @return boolean
     */
    protected function _toHtml()
    {
        if($this->isAllowed()){
          return parent::_toHtml();
        }
        else {
          return '';
        }
    }
}