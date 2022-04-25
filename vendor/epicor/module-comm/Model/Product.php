<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Product
 *
 * @author David.Wylie
 */
class Product extends \Magento\Catalog\Model\Product
{

    private $_skus;
    private $_skusDetailed;
    private $_locations=[];
    private $_deleteLocations;
    private $_customerLocations;
    protected $_hasLocationChanges = false;
    protected $_origData;
    protected $getDecimalPlacesExist = false;
    protected $catalogRuleRuleFactoryExist=null;
    protected $getCustomerLocationsExist=false;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory
     */
    protected $commResourceCustomerSkuCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link
     */
    protected $catalogResourceModelProductLink;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $catalogInventoryStockItemFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory
     */
    protected $commResourceLocationProductCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Location\ProductFactory
     */
    protected $commLocationProductFactory;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogResourceModelProductFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleRuleFactory;

    /**
     * @var \Epicor\Common\Helper\Messaging\Cache
     */
    protected $commonMessagingCacheHelper;
    
    protected $catalogResourceModelFactoryExist=null;

    protected $stkTypeExist=null;

    protected $getStockTypeExist=null;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var int
     */
    private $pricePrecision;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataService,
        \Magento\Catalog\Model\Product\Url $url,
        \Magento\Catalog\Model\Product\Link $productLink,
        \Magento\Catalog\Model\Product\Configuration\Item\OptionFactory $itemOptionFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\Catalog\Model\Product\OptionFactory $catalogProductOptionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\ResourceModel\Product $resource,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $resourceCollection,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $productEavIndexerProcessor,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Product\Image\CacheFactory $imageCacheFactory,
        \Magento\Catalog\Model\ProductLink\CollectionProvider $entityCollectionProvider,
        \Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
        \Magento\Catalog\Api\Data\ProductLinkExtensionFactory $productLinkExtensionFactory,
        \Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool $mediaGalleryEntryConverterPool,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Sku\CollectionFactory $commResourceCustomerSkuCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Link $catalogResourceModelProductLink,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $catalogInventoryStockItemFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory $commResourceLocationProductCollectionFactory,
        \Epicor\Comm\Model\Location\ProductFactory $commLocationProductFactory,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleRuleFactory,
        \Epicor\Common\Helper\Messaging\Cache $commonMessagingCacheHelper,
        \Epicor\AccessRight\Model\Authorization $accessauthorization,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
        $this->commResourceCustomerSkuCollectionFactory = $commResourceCustomerSkuCollectionFactory;
        $this->catalogResourceModelProductLink = $catalogResourceModelProductLink;
        $this->scopeConfig = $scopeConfig;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->registry = $registry;
        $this->commonHelper = $commonHelper;
        $this->catalogInventoryStockItemFactory = $catalogInventoryStockItemFactory;
        $this->storeManager = $storeManager;
        $this->commResourceLocationProductCollectionFactory = $commResourceLocationProductCollectionFactory;
        $this->commLocationProductFactory = $commLocationProductFactory;
        $this->commProductHelper = $commProductHelper;
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->catalogRuleRuleFactory = $catalogRuleRuleFactory;
        $this->commonMessagingCacheHelper = $commonMessagingCacheHelper;
        $this->_accessauthorization = $accessauthorization;
        $this->pricePrecision = $this->commHelper->getProductPricePrecision();
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $metadataService,
            $url,
            $productLink,
            $itemOptionFactory,
            $stockItemFactory,
            $catalogProductOptionFactory,
            $catalogProductVisibility,
            $catalogProductStatus,
            $catalogProductMediaConfig,
            $catalogProductType,
            $moduleManager,
            $catalogProduct,
            $resource,
            $resourceCollection,
            $collectionFactory,
            $filesystem,
            $indexerRegistry,
            $productFlatIndexerProcessor,
            $productPriceIndexerProcessor,
            $productEavIndexerProcessor,
            $categoryRepository,
            $imageCacheFactory,
            $entityCollectionProvider,
            $linkTypeProvider,
            $productLinkFactory,
            $productLinkExtensionFactory,
            $mediaGalleryEntryConverterPool,
            $dataObjectHelper,
            $joinProcessor,
            $data
        );
    }


    public function getCustomerSku($customerErpId = null, $first = false, $getAlternatives = false, $getDetails = false)
    {
        $arr = array();
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */

        if (is_null($customerErpId)) {
            $erpAccount = $commHelper->getErpAccountInfo();
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            if ($erpAccount) {
                $customerErpId = $erpAccount->getId();
            }
        }

        $collection = $this->commResourceCustomerSkuCollectionFactory->create();

        /* @var $collection Epicor_Comm_Model_Resource_Erp_Customer_Sku_Collection */

        $collection->addFieldToFilter('product_id', $this->_getCpnProductId());
        $collection->addFieldToFilter('customer_group_id', $customerErpId);

        foreach ($collection->getItems() as $cusSku) {
            if ($getDetails) {
                $arr[] = array(
                    'sku' => $commHelper->getSku($cusSku->getSku()),
                    'isCustomerSku' => true,
                    'entityId' => $cusSku->getEntityId()
                );
            } else {
                $arr[] = $commHelper->getSku($cusSku->getSku());
            }
        }

        if (empty($arr) && $getAlternatives) {
            $arr = $this->getAlternativeSku();
        }

        if ($first) {
            if (empty($arr)) {
                $arr = '';
            } else {
                $arr = array_pop($arr);
            }
        }

        return $arr;
    }

    public function getAlternativeSku($getDetails = false)
    {
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */
        $collection = $this->commResourceCustomerSkuCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Erp_Customer_Sku_Collection */
        $collection->addFieldToFilter('product_id', $this->_getCpnProductId());
        $collection->addFieldToFilter('customer_group_id', 0);

        $arr = array();
        foreach ($collection->getItems() as $cusSku) {
            if ($getDetails) {
                $arr[] = array(
                    'sku' => $commHelper->getSku($cusSku->getSku()),
                    'isCustomerSku' => false,
                    'entityId' => $cusSku->getEntityId()
                );
            } else {
                $arr[] = $commHelper->getSku($cusSku->getSku());
            }
        }
        return $arr;
    }

    private function _getCpnProductId()
    {
        $productId = $this->getId();
        $sku = $this->getSku();
        $commHelper = $this->commHelper;
        /* @var $commHelper Epicor_Comm_Helper_Data */
        $uomSep = $commHelper->getUOMSeparator();

        if (strpos($sku, $uomSep) !== false) {
            $parentIds = $this->catalogResourceModelProductLink
                ->getParentIdsByChild($this->getId(), \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);
            $productId = array_shift($parentIds);
        }

        return $productId;
    }

    /**
     * Updates a the passed array to contain the new sku values.
     * @param string $path
     * @return array
     */
    private function getSkuArray($path, &$skuArray, $getDetails = false)
    {
        $array = array();
        $append = $this->scopeConfig->isSetFlag('epicor_comm_field_mapping/cpn_mapping/sku_append', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($append || count($skuArray) == 0) {
            switch ($this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                case ('cussku'):
                    $array = $this->getCustomerSku(null, false, false, $getDetails);
                    break;
                case('altsku'):
                    $array = $this->getAlternativeSku($getDetails);
                    break;
                case('prodsku'):
                    //get product sku as array.
                    if ($getDetails) {
                        $array[] = array(
                            'sku' => $this->getSku(),
                            'isCustomerSku' => false,
                            'entityId' => null
                        );
                    } else {
                        $array[] = $this->getSku();
                    }
                    break;
            }
            if ($append) {
                $skuArray = array_merge($skuArray, $array);
            } else {
                $skuArray = $array;
            }
        }
    }

    public function getSkus()
    {
        if (!isset($this->_skus)) {
            $skus = array();
            $configpath = 'epicor_comm_field_mapping/cpn_mapping/';
            $this->getSkuArray($configpath . 'sku_primary', $skus);
            $this->getSkuArray($configpath . 'sku_secondary', $skus);
            $this->getSkuArray($configpath . 'sku_tertiary', $skus);
            $this->_skus = $skus;
        }
        return $this->_skus;
    }

    public function getSkusDetailed()
    {

        if (!isset($this->_skusDetailed)) {
            $skus = array();
            $configpath = 'epicor_comm_field_mapping/cpn_mapping/';
            $this->getSkuArray($configpath . 'sku_primary', $skus, true);
            $this->getSkuArray($configpath . 'sku_secondary', $skus, true);
            $this->getSkuArray($configpath . 'sku_tertiary', $skus, true);
            $this->_skusDetailed = $skus;
        }
        return $this->_skusDetailed;
    }

    public function getSkuHeader()
    {
        $locHelper = $this->commLocationsHelper;
        $count = count($this->getSkus());
        if ($count > 1) {
            return __('Product Codes:');
        } else if ($count == 1) {
            return __('Product Code:');
        }
    }

    public function getStockType()
    {

        if (!$this->getStockTypeExist) {
            $storeId = $this->storeManager->getStore()->getStoreId();
            $stockLevelDisplay = $this->catalogResourceModelFactory()
                ->getAttributeRawValue($this->getId(), 'ecc_stockleveldisplay', $storeId);
            $this->getStockTypeExist = ($stockLevelDisplay) ? $stockLevelDisplay :
                $this->scopeConfig->getValue(
                    'Epicor_Comm/stock_level/display',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
        }
        return $this->getStockTypeExist;
    }

    public function getStockLevel($summStock = null)
    {
        $locHelper = $this->commLocationsHelper;
        /*  @var $helper Epicor_Comm_Helper_Locations */
        $showLocations = $locHelper->isLocationsEnabled();
        $locations = $this->getCustomerLocations();
        $singleLocation = (count($locations) == 1) ? true : false;
        $allSourceLocations = ($this->scopeConfig->getValue(
            'epicor_comm_locations/global/stockvisibility',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) == 'all_source_locations') ? true : false;
        $stock = 0;
        $decimalPlaces = $this->getDecimalPlaces();
        if ($showLocations && ($allSourceLocations || $singleLocation || $summStock)) {
            $aggregateStockLevels = $this->registry->registry('aggregate_stock_levels_' . $this->getSku());
            if ($aggregateStockLevels && $locations) {
                $stock = $this->commLocationsHelper->aggregateLocationStockLevels(
                    $this->registry->registry('aggregate_stock_levels_' . $this->getSku()),
                    array_keys($locations)
                );
            }
        }
        if (!$stock) {
            if ($this->getErpStock() !== null) {
                $stock =  $this->getErpStock();
            } else {
                /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
                $stockItem = $this->catalogInventoryStockItemFactory->create();
                $stockItem->getResource()->loadByProductId($stockItem, $this->getId(), $stockItem->getStockId());
                $stock = $stockItem->getQty();
            }
        }
        $stock = $this->commonHelper->qtyRounding($stock, $decimalPlaces);
        return $stock;
    }

    private function getStockImage($level, $default = '')
    {
        $configImageFile = $this->scopeConfig->getValue("Epicor_Comm/stock_level/range_{$level}_img", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $imageFile = $configImageFile ?: $default;
        //M1 > M2 Translation Begin (Rule p2-5.3)
        //return Mage::getBaseUrl('media') . "catalog/stock/$level/$imageFile";
        return $this->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "catalog/stock/$level/$imageFile";
        //M1 > M2 Translation End
    }

    public function getStockRange()
    {

        $stock = $this->getStockLevel();
        $product = $this->catalogProductFactory->create()->load($this->getId());
        $rangeLowLimit = $product->getEccStocklimitlow();  // these won't be set from list page 
        $rangeNoneLimit = $product->getEccStocklimitnone();

        $rangeLowLimit = isset($rangeLowLimit) ? $rangeLowLimit : $this->scopeConfig->getValue('Epicor_Comm/stock_level/range_low_limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);  // if no value, use config value
        $rangeNoneLimit = isset($rangeNoneLimit) ? $rangeNoneLimit : $this->scopeConfig->getValue('Epicor_Comm/stock_level/range_none_limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($stock <= $rangeNoneLimit) {
            //no stock
            $img = $this->getStockImage('none', 'default/red.gif');
            $alt = __('Out of stock');
        } else if ($stock <= $rangeLowLimit) {
            //low stock
            $img = $this->getStockImage('low', 'default/amber.gif');
            $alt = __('Low stock');
        } else {
            //in stock
            $img = $this->getStockImage('high', 'default/green.gif');
            $alt = __('In Stock');
        }
        return array(
            'src' => $img,
            'alt' => $alt,
            'title' => $alt,
        );
    }

    public function isReorderable()
    {
        $sku = $this->getSku();
        $currentStore = $this->storeManager->getStore()->getId();
        $localSkusNotReorderable = $this->scopeConfig->getValue('Epicor_Comm/epicor_sku/reorderable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $currentStore);
        $excludeLocalSkus = strpos($localSkusNotReorderable, $sku);
        $trueFalse = false;
        if ($excludeLocalSkus === false) {                                              // if nothing at store level, get global level 
            $globalSkusNotReorderable = $this->scopeConfig->getValue('Epicor_Comm/epicor_sku/reorderable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
            $excludeGlobalSkus = strpos($globalSkusNotReorderable, $sku);
            if ($excludeGlobalSkus === false) {                                         // if not excluded globally, look at product attribute
                if ($this->getEccReorderable()) {
                    $trueFalse = true;                                                 // if product is reorderable, set to true
                }
            }
        }
        return $trueFalse;
    }

    /**
     * Gets all location data for this product (without extra info)
     * 
     * @return array
     */
    public function getLocationsWithoutExtra()
    {
        return $this->getLocations(false);
    }


    /**
     * Gets all location data for this product
     *
     * @return array
     */
    public function getAdminLocations($extraInfo = true)
    {
        $helper=$this->commLocationsHelper;
        if (empty($this->_locations)) {
            $this->_locations = array();
            $locations = $this->commResourceLocationProductCollectionFactory->create();
            /* @var $locations \Epicor\Comm\Model\ResourceModel\Location\Product\Collection */
            if($this->getId()) {
                if ($extraInfo) {
                    $locations->joinLocationInfo();
                    $locations->joinExtraProductInfo($this->getStoreId(), $this->getId());
                } else {
                    $locations->addFieldToFilter('main_table.product_id', $this->getId());
                }
            } else {
                $locations->addFieldToFilter('main_table.product_id', $this->getId());
            }

            foreach ($locations->getItems() as $location) {
                /* @var $location \Epicor\Comm\Model\Location\Product */
                $this->_locations[strval($location->getLocationCode())] = $location;
            }
        }
        return $this->_locations;
    }

    /**
     * Gets all location data for this product
     * 
     * @return array
     */
    public function getLocations($extraInfo = true)
    {
        $helper=$this->commLocationsHelper;
        if (empty($this->_locations)) {
            $this->_locations = array();
            $locations = $this->commResourceLocationProductCollectionFactory->create();
            /* @var $locations \Epicor\Comm\Model\ResourceModel\Location\Product\Collection */


            if ($extraInfo) {
                $locations->joinLocationInfo();
                if ($this->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE && !$this->commLocationsHelper->isLocationRequireForConfigurable()) { // pass child product id
                    $productID = false;
                    $allow = $this->registry->registry('Epicor_No_Valid_Qty_Selected');//skip pricing sku when product adding to cart
                    if($this->getEccPricingSku() && $allow === null) {
                        $productID = $this->getIdBySku($this->getEccPricingSku());
                    }

                    if(!$productID) {
                        $productID = $this->getIdBySku($this->getSku()) ?: $this->getId();
                    }
                    $locations->joinExtraProductInfo($this->getStoreId(), $productID);
                } else {
                    $locations->joinExtraProductInfo($this->getStoreId(), $this->getId());
                }
            } else {
                $locations->joinCompanyInfo();
                $locations->addFieldToFilter('main_table.product_id', $this->getId());
            }

            foreach ($locations->getItems() as $location) {
                /* @var $location \Epicor\Comm\Model\Location\Product */
                $this->_locations[strval($location->getLocationCode())] = $location;
            }
        }
        return $this->_locations;
    }

    /**
     * Gets all location data for this product
     * 
     * @return array
     */
    public function getLocation($code)
    {
        if (!isset($this->_locations[$code])) {
            $this->getLocations();
        }

        return isset($this->_locations[$code]) ? $this->_locations[$code] : false;
    }

    public function getCustomerLocations()
    {
        if ($this->getCustomerLocationsExist === false) {

            $helper = $this->commLocationsHelper;
            /* @var $helper Epicor_Comm_Helper_Locations */
            $allowed = $helper->getCustomerDisplayLocationCodes();
            $allowed = array_map('strval', $allowed);
            $allowed = array_map('strtoupper', $allowed);
            $locations = $this->getLocations();

            $customerLocations = array();
            if (is_array($locations)) {
                foreach ($locations as $location) {
                    /* @var $location Epicor_Comm_Model_Location_Product */
                    $locationCode = strval($location->getLocationCode());
                    $locationCode = strtoupper($locationCode);
                    if (in_array($locationCode, $allowed)) {
                        $customerLocations[strval($location->getLocationCode())] = $location;
                    }
                }
            }
            $this->getCustomerLocationsExist = $customerLocations;

        }

        /**
         * ECC should only list the locations
         * those have been returned in the MSQ response from ERP
         */
        if ($this->getCustomerLocationsExist) {
            $this->getCustomerLocationsExist = $this->commLocationsHelper->filterMsqLocations($this->getCustomerLocationsExist,
                $this);
        }

        return $this->getCustomerLocationsExist;
    }

    public function getStockedLocations()
    {
        $locations = $this->getCustomerLocations();

        foreach ($locations as $locationCode => $location) {
            if (!$location->isInStock()) {
                unset($locations[$locationCode]);
            }
        }

        return $locations;
    }

    /**
      /**
     * Sets the locations for this product
     * 
     * @param array $locations
     */
    public function setLocations($locations)
    {
        $currentLocations = $this->getLocations();
        $newLocations = array();

        foreach ($locations as $location) {
            $this->setLocationData($location->getLocationCode(), $location);
            $newLocations[] = $location->getLocationCode();
        }

        foreach ($currentLocations as $location) {
            /* @var $location Epicor_Comm_Model_Location_Product */
            if (!in_array($location->getLocationCode(), $newLocations)) {
                $this->deleteLocation($location->getLocationCode());
            }
        }

        $this->_hasLocationChanges = true;
    }

    /**
     * Sets the data for a location
     *
     * @param string $locationCode
     * @param array $data
     * @param array $storesdefaultCurrency
     * return Epicor_Comm_Model_Location_Product
     * @param array $canUpdate
     * @return Location\Product|mixed
     */
    public function setLocationData($locationCode, $data,$storesdefaultCurrency = array(), $canUpdate = array())
    {
        /* @var $location \Epicor\Comm\Model\Location\Product */
        $newStkLocation = false;
        if (isset($this->_locations[$locationCode])) {
            $location = $this->_locations[$locationCode];
        } else {
            $location = $this->commLocationProductFactory->create();
            $newStkLocation = true;
        }

        $location->checkAndSetProductId($this->getId());
        $location->checkAndSetLocationCode($locationCode);

        foreach ($canUpdate as $key => $value) {
            if ($value || $newStkLocation) {
                if ($key == "currencies") {
                    $location->setCurrencies($data[$key], $storesdefaultCurrency);
                } elseif ($key == "manufacturers") {
                    $location->checkAndSet($key, serialize($data[$key]));
                } else {
                    $location->checkAndSet($key, $data[$key]);
                }
            }
        }

        $this->_locations[$locationCode] = $location;

        $this->_hasLocationChanges = true;

        return $location;
    }

    /**
     * Sets the object for a location
     * 
     * @param string $locationCode
     * @param \Epicor\Comm\Model\Location\Product $location
     * 
     * return Epicor_Comm_Model_Location_Product
     */
    public function setLocation($locationCode, $location)
    {
        /* @var $location \Epicor\Comm\Model\Location\Product */
        $this->_locations[$locationCode] = $location;

        $this->_hasLocationChanges = true;

        return $location;
    }

    /**
     * Moves a location to the deleted array for deletion when product saves
     * 
     * @param string $locationCode
     */
    public function deleteLocation($locationCode)
    {
        if (isset($this->_locations[$locationCode])) {
            $this->_deleteLocations[] = $this->_locations[$locationCode];
            unset($this->_locations[$locationCode]);
            $this->_hasLocationChanges = true;
        }
    }

    public function isValidLocation($locationCode)
    {
        $currentLocations = $this->getCustomerLocations();
        return isset($currentLocations[$locationCode]);
    }

    public function saveOrigData()
    {
        if ($this->_origData == null) {
            $this->_origData = $this->getData();
        }
        return $this;
    }

    public function readOrigData()
    {
        return $this->_origData;
    }

    public function restoreOrigData()
    {
        $this->setData($this->_origData);
        return $this;
    }


    public function stkTypeValue($storeId)
    {

        if (!$this->stkTypeExist) {
            $this->stkTypeExist = $this->catalogResourceModelProductFactory->create()
                ->getAttributeRawValue($this->getId(), 'ecc_stk_type', $storeId);;
        }
        return $this->stkTypeExist;
    }

    public function getDecimalPlaces()
    {
        if ($this->getDecimalPlacesExist === false) {
            $this->getDecimalPlacesExist = $this->commMessagingHelper->getDecimalPlaces($this);
        }
        return $this->getDecimalPlacesExist;
    }

    /**
     * Pass either a Location_Product Object and set those values or 
     * pass a location_code and that location data will be loaded from the product.
     * 
     * @param \Epicor\Comm\Model\Location\Product|string $location
     */
    public function setToLocationPrices($location)
    {
        \Magento\Framework\Profiler::start('setToLocationPrices');
        $productHelper = $this->commProductHelper;
        /* @var $productHelper \Epicor\Comm\Helper\Product */

        $store = $this->storeManager->getStore();
        $storeId = $store->getStoreId();
        $stkType = $this->stkTypeValue($storeId);

        //Send Non-ERP products in MSQ - If the product was created in Magento
        $msqForNonErp = $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/msq_for_non_erp_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ((!$msqForNonErp) && (!$stkType)) {
            return;
        }

        $locHelper = $this->commLocationsHelper;
        /*  @var $helper \Epicor\Comm\Helper\Locations */
        $showLocations = $locHelper->isLocationsEnabled();
        if (!$showLocations) {
            return;
        }
        if (!$this->hasData('main_product_is_saleable')) {
            $this->setMainProductIsSaleable($this->isSaleable());
        }

        $this->saveOrigData();

        if (is_string($location)) {
            $location = $this->getLocation($location);
        }
        if ($location instanceof \Epicor\Comm\Model\Location\Product) {
            $productCurrency = $location->getCurrency($store->getBaseCurrencyCode());
        } else {
            $productCurrency = false;
        }
        $this->setIsLocationPriceApplied(false);
        if ($productCurrency !== false) {
            $basePrice = $productCurrency->getBasePrice();
            $customerPrice = $productCurrency->getCustomerPrice();
            if(in_array(\Epicor\Comm\Model\Message\Request\Msq::MESSAGE_TYPE,$this->commHelper->getArrayMessages())){
                $breakInfo = $this->_processProductBreaksArray($productCurrency, $customerPrice, $this);
            }else{
                $breakInfo = $this->_processProductBreaks($productCurrency, $customerPrice, $this);
            }
            $roundingDecimals = $this->getPreventRounding() ? '4' : $this->pricePrecision;
            $productHelper->setProductPrices($this, $basePrice, $customerPrice, $productCurrency->getBreaks(), $roundingDecimals, true);

            $stockData = $this->getStockData();

            $min = $location->getMinimumOrderQty();
            $max = $location->getMaximumOrderQty();

            if (!empty($min)) {
                $stockData['min_sale_qty'] = $min;
            }

            if (!empty($max)) {
                $stockData['max_sale_qty'] = $max;
            }
            $helper = $this->commMessagingHelper;
            /* @var $helper \Epicor\Comm\Helper\Messaging */
            $decimalPlaces = $this->getDecimalPlaces();
            if ($locHelper->getStockVisibilityFlag() == 'all_source_locations') {
                $stockData['qty'] = $this->getStockLevel();
            } else {
                $stockData['qty'] = $helper->qtyRounding($location->getFreeStock(), $decimalPlaces);
            }


            $this->setStockData($stockData);
            $this->setEccLeadTime($location->getLeadTime());

            // Set availability
            $msqAlwaysInStock = $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/products_always_in_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
            $stockItem = $this->catalogInventoryStockItemFactory->create();
            $stockItem->getResource()->loadByProductId($stockItem, $this->getId(), $stockItem->getStockId());
            $msqAlwaysInStock = $msqAlwaysInStock || $stockItem->getBackorders();

            /**
             * First check discontinued is true then return false else
             * Second return msq config always in stock
             */
            $msqAlwaysInStock = $this->getIsEccDiscontinued() ? false : $msqAlwaysInStock; // discontinued item

            /**
             * First check non-stock is true then return true else
             * Second return parent var alwaysInStock
             */
            $msqAlwaysInStock = $this->getIsEccNonStock() ?: $msqAlwaysInStock; // non-stock item
            if ($this->getMainProductIsSaleable()) {
                $in_stock = $stockData['qty'] > 0 ? true : $msqAlwaysInStock;
                if ($this->getTypeId() == 'configurable') {
                    $in_stock = true;
                }
                $this->setSalable($in_stock);
            }
            $this->setErpStock($stockData['qty']);

            $this->setIsLocationPriceApplied(true);
        } else {
            $this->restoreOrigData();
        }
        \Magento\Framework\Profiler::stop('setToLocationPrices');

    }

    public function forceMainProductToBeSaleable($bool)
    {
        if ($bool) {
            $this->setData('is_salable', true);
        }
    }


    public function catalogRuleRuleFactory()
    {
        if (!$this->catalogRuleRuleFactoryExist) {
            $this->catalogRuleRuleFactoryExist = $this->catalogRuleRuleFactory->create();
        }
        return $this->catalogRuleRuleFactoryExist;
    }
    /**
     * Deals with break information in a product array response
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     * @param integer $customerPrice
     * 
     * @return array
     */
    private function _processProductBreaksArray($currencyInfo, $customerPrice, $product, $hideTier = null)
    {
        $store = $this->storeManager->getStore();
        /* @var $store \Epicor\Comm\Model\Store */
        $rule = $this->catalogRuleRuleFactory();
        /* @var $rule \Magento\CatalogRule\Model\Rule */
        $minimalPrice = $customerPrice;

        $tierPrices = array();

        //Rounding must be done in the msq before the BSV 
        //_sendMsqForBsvItems
        $preventRounding = ($this->getPreventRounding()) ? "4" : "2";

        if (isset($currencyInfo['breaks']) && isset($currencyInfo['breaks']['break']) ) {
            $qtyOne = false;
            $breaksArray = (isset($currencyInfo['breaks']['break']))?$currencyInfo['breaks']['break']:[];
            if(!empty($breaksArray) && !isset($breaksArray[0])){
                $temp = $breaksArray;
                $breaksArray = [];
                $breaksArray[0] = $temp;
            }
            foreach ($breaksArray as $break) {
                if (is_array($break['price']) && empty($break['price'])) {
                    continue;
                }
                $price = $rule->calcProductPriceRule($product, $break['price']);
                if (!$price) {
                    $price = $break['price'];
                }

                if ($break['quantity'] > 1) {
                    $tierPrices[] = array(
                        'website_id' => 0,
                        'cust_group' => 32000, // All groups
                        'price_qty' => $break['quantity'],
                        'price' => $store->roundPrice($price, $preventRounding),
                        'website_price' => $store->roundPrice($price, $preventRounding)
                    );

                    $minimalPrice = ($minimalPrice > $price) ? $price : $minimalPrice;
                } else if ($break['quantity'] == 1) {
                    if (!$hideTier) {
                        $customerPrice = $break['price'];
                    }
                }
            }
        }
        return array(
            'tierPrices' => ($hideTier) ? array() : $tierPrices, //If hidetier =1 then don't show tier prices
            'customerPrice' => $customerPrice,
            'minimalPrice' => $minimalPrice,
        );
    }

    /**
     * Deals with break information in a product Object response
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpProduct
     * @param integer $customerPrice
     * 
     * @return array
     */
    
    private function _processProductBreaks($currencyInfo, $customerPrice, $product, $hideTier = null)
    {
        $store = $this->storeManager->getStore();
        /* @var $store \Epicor\Comm\Model\Store */
        $rule = $this->catalogRuleRuleFactory->create();
        /* @var $rule \Magento\CatalogRule\Model\Rule */
        $minimalPrice = $customerPrice;

        $tierPrices = array();

        //Rounding must be done in the msq before the BSV 
        //_sendMsqForBsvItems
        $preventRounding = ($this->getPreventRounding()) ? "4" : "2";

        if ($currencyInfo->getBreaks() != null &&
            $currencyInfo->getBreaks()->getBreak() != null) {
            $qtyOne = false;
            $breaksArray = $currencyInfo->getBreaks()->getasarrayBreak();

            foreach ($breaksArray as $break) {
                $price = $rule->calcProductPriceRule($product, $break->getPrice());
                if (!$price) {
                    $price = $break->getPrice();
                }

                if ($break->getQuantity() > 1) {
                    $tierPrices[] = array(
                        'website_id' => 0,
                        'cust_group' => 32000, // All groups
                        'price_qty' => $break->getQuantity(),
                        'price' => $store->roundPrice($price, $preventRounding),
                        'website_price' => $store->roundPrice($price, $preventRounding)
                    );

                    $minimalPrice = ($minimalPrice > $price) ? $price : $minimalPrice;
                } else if ($break->getQuantity() == 1) {
                    if (!$hideTier) {
                        $customerPrice = $break->getPrice();
                    }
                }
            }
        }

        return array(
            'tierPrices' => ($hideTier) ? array() : $tierPrices, //If hidetier =1 then don't show tier prices
            'customerPrice' => $customerPrice,
            'minimalPrice' => $minimalPrice,
        );
    }

    /**
     * Process locations after the product is saved
     */
    public function afterSave()
    {
        if (is_array($this->_locations)) {
            foreach ($this->_locations as $location) {
                /* @var $location \Epicor\Comm\Model\Location\Product */
                $location->save();
            }
        }

        if (is_array($this->_deleteLocations)) {
            foreach ($this->_deleteLocations as $location) {
                /* @var $location \Epicor\Comm\Model\Location\Product */
                if (!$location->isObjectNew()) {
                    $location->delete();
                }
            }
        }

        if ($this->_hasLocationChanges) {
            $cacheHelper = $this->commonMessagingCacheHelper;
            /* @var $cacheHelper \Epicor\Common\Helper\Messaging\Cache */

            $cacheHelper->deleteCache(array($this->getSku()));
        }

        parent::afterSave();
    }

    public function _beforeDelete()
    {
        if (is_array($this->getLocations())) {
            foreach ($this->getLocations() as $location) {
                /* @var $location \Epicor\Comm\Model\Location\Product */
                $location->delete();
            }
        }
        parent::_beforeDelete();
    }

    public function getRequiredLocation()
    {
        $customerLocations = $this->getCustomerLocations();
        $singleLocation = count($customerLocations) == 1;

        $stockVisibility = $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (in_array($stockVisibility, (array('all_source_locations', 'default')))) {            // if true return default location     
            return $this->commLocationsHelper->getDefaultLocationCode();
        }
        if ($singleLocation) {
            $location = array_pop($customerLocations);
            return $location->getLocationCode();
        }

        // if not single location or above stock visibility not set
        $location = $this->registry->registry('current_location') ?: $this;
        return $location->getLocationCode();
    }

    /**
     * Calculates Epicor Original Price with current prices
     * 
     * @param  float $qty
     * @return float $price
     */
    public function calculateEpicorOriginalPrice($qty)
    {
        $price = $this->getBasePrice();
        $tierPrice = $this->getTierPrice($qty);
        $store = $this->storeManager->getStore();
        if (is_array($tierPrice) && !empty($tierPrice) && isset($tierPrice[0]['website_price'])) {
            $tierPrice = $tierPrice[0]['website_price'];
        }

        if (!is_null($tierPrice)) {
            if ($tierPrice > $price) {
                //BSV when base price is less than customer price in MSQ - ECC sending incorrect BSV
                $price = $tierPrice;
            } else {
                $price = min($price, $tierPrice);
            }
        }

        $special = $this->getSpecialPrice();
        if (!is_null($special)) {
            $price = min($price, $special);
        }

        return $store->roundPrice($price, $this->pricePrecision);
    }
      public function catalogResourceModelFactory() {
        if (!$this->catalogResourceModelFactoryExist) {
            $this->catalogResourceModelFactoryExist = $this->catalogResourceModelProductFactory->create();
        }
        return $this->catalogResourceModelFactoryExist;
    }

    public function getLocationsForAggregateStock()
    {
        if ($this->registry->registry('inventory_view_locations')) {
            $allowed = $this->registry->registry('inventory_view_locations');
        } else {
            $helper = $this->commLocationsHelper;
            /* @var $helper Epicor_Comm_Helper_Locations */
            $allowed = $helper->getCustomerDisplayLocationCodes();
            $allowed = array_map('strval', $allowed);
        }

        $locations = $this->getLocations();

        $customerLocations = array();
        if (is_array($locations)) {
            foreach ($locations as $location) {
                /* @var $location Epicor_Comm_Model_Location_Product */
                if (in_array(strval($location->getLocationCode()), $allowed) && $location->getIncludeInventory()) {
                    $customerLocations[strval($location->getLocationCode())] = $location;
                }
            }
        }

        return $customerLocations;
    }

    /**
     * @return \Magento\Framework\AuthorizationInterface
     */
    public function getAccessAuthorization()
    {
        return $this->_accessauthorization;
    }

    /**
     * Check is product available for sale
     *
     * @return bool
     */
    public function isSaleable()
    {
        //Added to handle out of stock products
        $outOfStockNotAllowed = $this->getIsEccNonStock() ? false : !$this->commHelper->isShowOutOfStock();
        if ($outOfStockNotAllowed) {
            $remove = $this->registry->registry('hide_out_of_stock_product');
            if($remove && in_array($this->getId(), $remove)){
                return false;
            }
        }
        $canHidePrice = $this->commHelper->getEccHidePrice() ?: 0;
        if ($canHidePrice && $canHidePrice != 3 && $this->getTypeId() != 'configurable') {
            return false;
        }
        if (!$this->_accessauthorization->isAllowed(
            'Epicor_Checkout::checkout_checkout_can_checkout'
        )) {
            return false;
        }
        return parent::isSaleable();
    }
}
