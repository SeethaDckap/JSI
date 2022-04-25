<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer;


/**
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method void setOnRight(bool $bool)
 * @method bool getOnRight()
 */
class Address extends \Magento\Framework\View\Element\Template
{

    /**
     *  @var \Magento\Framework\DataObject
     */
    protected $_addressData;
    protected $_countryCode;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->_addressData = $this->dataObjectFactory->create();
        $this->setTemplate('supplierconnect/address.phtml');
        $this->setOnRight(false);
    }

    public function getName()
    {
        return $this->_addressData->getName();
    }

    public function getStreet()
    {
        $street = $this->_addressData->getData('address1');
        $street .= $this->_addressData->getData('address2') ? ', ' . $this->_addressData->getData('address2') : '';
        $street .= $this->_addressData->getData('address3') ? ', ' . $this->_addressData->getData('address3') : '';
        return $street;
    }

    public function getCity()
    {
        return $this->_addressData->getCity();
    }

    public function getCounty()
    {
        $helper = $this->supplierconnectHelper;
        $region = $helper->getRegionFromCountyName($this->getCountryCode(), $this->_addressData->getCounty());

        return ($region) ? $region->getName() : $this->_addressData->getCounty();
    }

    public function getPostcode()
    {
        return $this->_addressData->getPostcode();
    }

    public function getCountryCode()
    {

        if (is_null($this->_countryCode)) {
            $helper = $this->supplierconnectHelper;
            $this->_countryCode = $helper->getCountryCodeForDisplay($this->_addressData->getCountry(), $helper::ERP_TO_MAGENTO);
        }

        return $this->_countryCode;
    }

    public function getCountry()
    {
        try {
            $helper = $this->supplierconnectHelper;
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

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getAddressData()
    {
        return $this->_addressData;
    }
}
