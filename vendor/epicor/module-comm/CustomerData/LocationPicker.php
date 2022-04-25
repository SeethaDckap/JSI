<?php
/**
 * Copyright © 2010-2019 Epicor Software. All rights reserved.
 */
namespace Epicor\Comm\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class LocationPicker implements SectionSourceInterface
{
    protected $_locationHelper;
    
    protected $_urlInterface;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;    
    
    public function __construct(
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Epicor\Comm\Helper\Data $commHelper
    ) {
        $this->commLocationsHelper = $commLocationsHelper;
        $this->_urlInterface = $urlInterface;
        $this->commHelper = $commHelper;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return [
            'items' => $this->getItems(),
            'returnUrl' => $this->getReturnUrl(),
            'chosenLocations' => $this->isLocationSelected()
        ];
    }
    
    /**
     * 
     * @param string $code
     * 
     * @return boolean
     */
    public function isLocationSelected()
    {
        return $this->getLocationHelper()->getCustomerDisplayLocationCodes();
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
    
    public function getReturnUrl()
    {
        $url = $this->_urlInterface->getCurrentUrl();
        return $this->commHelper->getUrlEncoder()->encode($url);
    }    
    
    /**
     * Get list of locations
     *
     * @return array
     */
    protected function getItems()
    {
        $items = [];
        $locations = $this->getCustomerAllowedLocations();
        foreach ($locations as $item) {
            $items[] = [
                'code' => $item->getCode(),
                'name' => $item->getName(),
            ];
        }
        return $items;
    }    
}