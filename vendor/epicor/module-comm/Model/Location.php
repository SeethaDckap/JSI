<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model;


/**
 * Location model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method setCode(string $value);
 * @method setName(string $value);
 * @method setAddress1(string $value);
 * @method setAddress2(string $value);
 * @method setAddress3(string $value);
 * @method setCity(string $value);
 * @method setCounty(string $value);
 * @method setCountry(string $value);
 * @method setPostcode(string $value);
 * @method setTelephoneNumber(string $value);
 * @method setFaxNumber(string $value);
 * @method setEmailAddress(string $value);
 * @method setDeliveryMethodCodes(string $value);
 * @method setCreatedAt(datetime $value)
 * @method setUpdatedAt(datetime $value)
 * @method setDummy(int $value)
 * @method setSource(string $value)
 * @method setMobileNumber(string $value)
 * 
 * @method string getCode();
 * @method string getName();
 * @method string getAddress1();
 * @method string getAddress2();
 * @method string getAddress3();
 * @method string getCity();
 * @method string getCounty();
 * @method string getCountry();
 * @method string getPostcode();
 * @method string getTelephoneNumber();
 * @method string getFaxNumber();
 * @method string getEmailAddress();
 * @method string getDeliveryMethodCodes();
 * @method datetime getCreatedAt()
 * @method datetime getUpdatedAt()
 * @method integer getDummy();
 * @method string getSource()
 * @method string getMobileNumber()
 */
class Location extends \Epicor\Common\Model\AbstractModel
{

    protected $_eventPrefix = 'ecc_location';
    protected $_eventObject = 'location';
    protected $_links = array();
    protected $_newLinks = array();
    protected $_deleteLinks = array();
    protected $_location_products = null;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory
     */
    protected $commResourceLocationProductCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Link\CollectionFactory
     */
    protected $commResourceLocationLinkCollectionFactory;
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\Currency\CollectionFactory
     */
    protected $collectionLocationProductCurrencyFactory;

    public function __construct(
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory $commResourceLocationProductCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Link\CollectionFactory $commResourceLocationLinkCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Product\Currency\CollectionFactory $collectionLocationProductCurrencyFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->commResourceLocationProductCollectionFactory = $commResourceLocationProductCollectionFactory;
        $this->commResourceLocationLinkCollectionFactory = $commResourceLocationLinkCollectionFactory;
        $this->collectionLocationProductCurrencyFactory = $collectionLocationProductCurrencyFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Location');
    }

    public function beforeSave()
    {
        $this->_checkCounty();

        parent::beforeSave();
    }

    private function _checkCounty()
    {
        $origCountryCode = $this->getOrigData('country');
        $countryCode = $this->getCountry();
        $origCountyCode = $this->getOrigData('county_code');
        $countyCode = $this->getCountyCode();

        if ($origCountryCode != $countryCode || $origCountyCode != $countyCode) {

            $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
            /* @var $countryModel \Magento\Directory\Model\Country */

            $collection = $this->directoryRegionFactory->create()->getResourceCollection()
                ->addCountryFilter($countryModel->getId())
                ->load();
            /* @var $collection \Magento\Directory\Model\ResourceModel\Region\Collection */

            // Check to see if the country has regions, and check if it's valid
            if ($collection->count() > 0) {
                // try loading a region with the county field as the code
                $region = $this->directoryRegionFactory->create()->loadByCode($countyCode, $countryModel->getId());
                /* @var $region \Magento\Directory\Model\Region */

                if (!empty($region) && !$region->isObjectNew()) {
                    $this->setCounty($region->getName());
                } else {
                    // try loading a region with the county field as the name
                    $address_county = '';
                    $region = $this->directoryRegionFactory->create()->loadByName($address_county, $countryModel->getId());

                    if (!empty($region) && !$region->isObjectNew()) {
                        $this->setCounty($region->getName())
                            ->setCountyCode($region->getCode());
                    }
                }
            } else {
                $this->setCounty($countyCode);
            }
        }
    }

    public function getStoreLinks()
    {
        return $this->getLinks(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_STORE);
    }

    public function addStore($storeId)
    {
        $this->addLink(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_STORE, $storeId, \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE);
    }

    public function deleteStore($storeId)
    {
        $this->deleteLink(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_STORE, $storeId);
    }

    public function addErpAccount($entityId, $linkType)
    {
        $this->addLink(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_ERPACCOUNT, $entityId, $linkType);
    }

    public function deleteErpAccount($entityId)
    {
        $this->deleteLink(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_ERPACCOUNT, $entityId);
    }

    public function addCustomer($entityId, $linkType)
    {
        $this->addLink(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER, $entityId, $linkType);
    }

    public function deleteCustomer($entityId)
    {
        $this->deleteLink(\Epicor\Comm\Model\Location\Link::ENTITY_TYPE_CUSTOMER, $entityId);
    }

    public function getLocationProducts()
    {
        if (!$this->_location_products && (!empty($this->getCode()))) {
            $locationProductCollection = $this->commResourceLocationProductCollectionFactory->create();
            /* @var $locationProductCollection \Epicor\Comm\Model\ResourceModel\Location\Product\Collection */
            $locationProductCollection->addFieldToFilter('location_code', $this->getCode());

            $this->_location_products = $locationProductCollection->getItems();
        }

        return $this->_location_products;
    }

    /*
     * get All data from ecc_location_product_currency table based on location code
     */
    public function getLocationProductsCurrencies(){
        $location_product_currency = array();
        if(!empty($this->getCode())){
            $locationProductCurrencyCollection = $this->collectionLocationProductCurrencyFactory->create();
            $locationProductCurrencyCollection->addFieldToFilter('location_code', $this->getCode());
            $location_product_currency = $locationProductCurrencyCollection->getItems();
        }

        return $location_product_currency;
    }

    /**
     * 
     * @param string $type
     * @return Array
     */
    public function getLinks($type = null)
    {
        if ($type == null) {
            foreach (\Epicor\Comm\Model\Location\Link::getEntityTypes() as $entityTypes) {
                $this->getLinks($entityTypes);
            }
            return $this->_links;
        } else {

            if (!isset($this->_links[$type]) || empty($this->_links[$type])) {
                $links = $this->commResourceLocationLinkCollectionFactory->create();
                /* @var $links \Epicor\Comm\Model\ResourceModel\Location\Link\Collection */

                $links->addFieldToFilter('entity_type', $type);
                $links->addFieldToFilter('location_code', $this->getCode());

                $this->_links[$type] = $links->getItems();
            }
            return $this->_links[$type];
        }
    }

    public function addLink($type, $entityId, $linkType)
    {
        if (!isset($this->_newLinks[$type])) {
            $this->_newLinks[$type] = array();
        }

        $this->_newLinks[$type][$entityId] = $linkType;
    }

    public function deleteLink($type, $entityId)
    {
        if (!isset($this->_deleteLinks[$type])) {
            $this->_deleteLinks[$type] = array();
        }

        $this->_deleteLinks[$type][] = $entityId;
    }

    public function setFullStores($storeIds)
    {
        $links = $this->getStoreLinks();

        $currentStores = array();

        foreach ($links as $link) {
            /* @var $link \Epicor\Comm\Model\Location\Link */
            if (!in_array($link->getEntityId(), $storeIds)) {
                $this->deleteStore($link->getEntityId());
            } else {
                $currentStores[] = $link->getEntityId();
            }
        }

        foreach ($storeIds as $storeId) {
            if (!in_array($storeId, $currentStores)) {
                $this->addStore($storeId);
            }
        }
    }

//    protected function _beforeDelete()
//    {
//        foreach ($this->getLinks() as $linkType) {
//            /* @var $linkType Array */
//            foreach ($linkType as $link) {
//                /* @var $link \Epicor\Comm\Model\Location\Link */
//                $link->delete();
//            }
//        }
//
//        foreach ($this->getLocationProducts() as $locationProduct) {
//            /* @var $locationProduct \Epicor\Comm\Model\Location\Product */
//            $locationProduct->delete();
//        }
//
//        parent::_beforeDelete();
//    }

    public function beforeDelete()
    {
        if($this->getId()){
            $locationModel =  $this->load($this->getId());
            foreach ($this->getLinks() as $linkType) {
                /* @var $linkType Array */
                foreach ($linkType as $link) {
                    /* @var $link \Epicor\Comm\Model\Location\Link */
                    $link->delete();
                }
            }

            foreach ($this->getLocationProducts() as $locationProduct) {
                /* @var $locationProduct \Epicor\Comm\Model\Location\Product */
                $locationProduct->delete();
            }

            foreach ($this->getLocationProductsCurrencies() as $locationProductCurrency) {
                /* @var $locationProduct \Epicor\Comm\Model\Location\Product\Currency */
                $locationProductCurrency->delete();
            }
        }
        return parent::beforeDelete();
    }

    public function afterSave()
    {
        foreach ($this->_newLinks as $type => $links) {
            foreach ($links as $entityId => $linkType) {
                $linkSearch = $this->commResourceLocationLinkCollectionFactory->create();
                /* @var $linkSearch \Epicor\Comm\Model\ResourceModel\Location\Link\Collection */
                $linkSearch->addFieldToFilter('location_code', $this->getCode());
                $linkSearch->addFieldToFilter('entity_id', $entityId);
                $linkSearch->addFieldToFilter('entity_type', $type);
                $link = $linkSearch->getFirstItem();
                /* @var $link \Epicor\Comm\Model\Location\Link */
                $link->setEntityType($type);
                $link->setEntityId($entityId);
                $link->setLocationCode($this->getCode());
                $link->setLinkType($linkType);
                $link->save();
            }
        }

        foreach ($this->_deleteLinks as $type => $links) {
            foreach ($links as $entityId) {
                $linkSearch = $this->commResourceLocationLinkCollectionFactory->create();
                /* @var $linkSearch \Epicor\Comm\Model\ResourceModel\Location\Link\Collection */
                $linkSearch->addFieldToFilter('location_code', $this->getCode());
                $linkSearch->addFieldToFilter('entity_id', $entityId);
                $linkSearch->addFieldToFilter('entity_type', $type);
                $link = $linkSearch->getFirstItem();
                /* @var $link \Epicor\Comm\Model\Location\Link */
                if (!$link->isObjectNew()) {
                    $link->delete();
                }
            }
        }
        parent::afterSave();
    }

}
