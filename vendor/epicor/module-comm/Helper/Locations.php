<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper;


class Locations extends \Epicor\Comm\Helper\Data
{

    protected $_locations;
    protected $_displayLocations;
    protected $_allowedLocations;
    protected $_productAllowedLocations = array();

    /**
     * FRONTEND FUNCTIONS
     */

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory
     */
    protected $commResourceLocationProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Location\ProductFactory
     */
    protected $commLocationProductFactory;
    
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Relatedlocations\CollectionFactory
     */
    protected $commResourceRelatedLocationCollectionFactory;
    
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Grouplocations\CollectionFactory
     */
    protected $commResourceGrouplocationsCollectionFactory;
    
    /**
     * @var \Epicor\Comm\Model\Location\RelatedlocationsFactory
     */
    protected $commRelatedLocationFactory;
    
    /**
     * @var \Epicor\Comm\Model\Location\GrouplocationsFactory
     */
    protected $commGrouplocationFactory;

    protected $customerSessionFactoryExist = null;

    protected $isLocationsEnabledExist = null;

    /**
     * @var \Epicor\Comm\Model\Location\LocationFilter
     */
    private $locationFilter;

    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Common\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Product\CollectionFactory $commResourceLocationProductCollectionFactory,
        \Epicor\Comm\Model\Location\ProductFactory $commLocationProductFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Relatedlocations\CollectionFactory $commResourceRelatedLocationCollectionFactory,
        \Epicor\Comm\Model\Location\RelatedlocationsFactory $commRelatedLocationFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Grouplocations\CollectionFactory $commResourceGrouplocationsCollectionFactory,
        \Epicor\Comm\Model\Location\GrouplocationsFactory $commGrouplocationFactory,
        \Epicor\Comm\Model\Location\LocationFilter $locationFilter
    ) {
        $this->commLocationFactory = $commLocationFactory;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commResourceLocationProductCollectionFactory = $commResourceLocationProductCollectionFactory;
        $this->commLocationProductFactory = $commLocationProductFactory;
        $this->commResourceRelatedLocationCollectionFactory = $commResourceRelatedLocationCollectionFactory;
        $this->commRelatedLocationFactory = $commRelatedLocationFactory;
        $this->commResourceGrouplocationsCollectionFactory = $commResourceGrouplocationsCollectionFactory;
        $this->commGrouplocationFactory = $commGrouplocationFactory;
        $this->locationFilter = $locationFilter;
        $this->directoryHelper = $context->getDirectoryHelper();
        parent::__construct($context);
    }
    /**
     * Is locations enabled for this customer?
     * 
     * (Only returns the flag at the mo, but may do more in future)
     * @return boolean
     */
    public function isLocationsEnabled()
    {
        if (!$this->isLocationsEnabledExist) {
            $this->isLocationsEnabledExist = $this->scopeConfig->isSetFlag(
                'epicor_comm_locations/global/enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->isLocationsEnabledExist;
    }

    public function showIn($area)
    {
        return $this->isLocationsEnabled() ? $this->scopeConfig->isSetFlag('epicor_comm_locations/frontend/display_in_' . $area, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : false;
    }

    public function showColumnIn($area)
    {
        return $this->showIn($area) ? $this->scopeConfig->isSetFlag('epicor_comm_locations/frontend/display_in_' . $area . '_column', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : false;
    }

    public function isLocationStockHidden()
    {
       return $this->isLocationsEnabled() && $this->isRequestCheckoutConfigure();
    }

    private function isRequestCheckoutConfigure()
    {
        $actionModule = $this->_getRequest()->getActionName() . '-' . $this->_getRequest()->getModuleName();
        return $actionModule === 'configure-checkout';
    }

    /**
     * Gets the name of a location form the given code
     * 
     * @param string $locationCode
     * 
     * @return string
     */
    public function getLocationName($locationCode)
    {
        if (!isset($this->_locations[$locationCode])) {
            $location = $this->commLocationFactory->create();
            /* @var $location Epicor_Comm_Model_Location */
            $location->load($locationCode, 'code');
            $this->_locations[$locationCode] = $location;
        } else {
            $location = $this->_locations[$locationCode];
        }

        return $location->getName();
    }

    public function getCustomerAllowedLocationCodes()
    {
        return $this->getCustomerAllowedLocations(true);
    }

    /**
     * Gets the current logged in customers allowed locations
     * 
     * @param boolean $codes - return codes or whole data
     * 
     * @return array
     */
    public function getCustomerAllowedLocations($codes = false)
    {
        if (is_null($this->_allowedLocations)) {
            $this->_allowedLocations = $this->_getCustomerAllowedLocations();
        }

        return $codes ? array_keys($this->_allowedLocations) : $this->_allowedLocations;
    }

    /**
     * Gets the current logged in cusotmers allowed locations
     * 
     * // alter the customers available locations based on the stock visibility setting
     * 
     * //"default" // Default Locations
     * //"logged_in_shopper_source" // Logged In Shopper Source Location
     * //"all_source_locations" // All Source Locations
     * //"all_given_company" //All Locations for a Given Company
     * //"locations_to_include" // List of Specific Locations to Include
     * //"locations_to_exclude" // List of Specific Locations to Exclude
     * 
     * @return array
     */
    public function _getCustomerAllowedLocations()
    {
        $session = $this->customerSessionFactory();
        /* @var $session \Magento\Customer\Model\Session */
        $customer = $session->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */
        $availableLocations = array();
        $locationCodes = array();
        //create available location codes
        $stockVisibility = $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        switch ($stockVisibility) {
            case 'default':
                $locationCodes[] = $this->getDefaultLocationCode();
                break;
            case 'logged_in_shopper_source':
                $defaultLocation = $this->getDefaultLocationCodeNoGlobal();
                if (empty($defaultLocation)) {
                    $availableLocations = $customer->getAllowedLocations();
                } else {
                    $locationCodes[] = $this->getDefaultLocationCode();
                }
                break;
            case 'locations_to_include':
                $locationsToInclude = $this->scopeConfig->getValue('epicor_comm_locations/global/locations_to_include', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $locationCodesToInclude = explode(',', $locationsToInclude);
                $customerLocations = array_keys($customer->getAllowedLocations());
                $locationCodes = array_intersect($locationCodesToInclude, $customerLocations);
                break;
            case 'locations_to_exclude':
                $locationsToExclude = $this->scopeConfig->getValue('epicor_comm_locations/global/locations_to_exclude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $locationCollection = $this->commResourceLocationCollectionFactory->create();
                /* @var $locationCollection Epicor_Comm_Model_Resource_Location_Collection */

                //limit locations to customer allowed locations
                $customerLocations = array_keys($customer->getAllowedLocations());
                $locationCollection->addFieldToFilter('code', array('in' => $customerLocations));
                if (!empty($locationsToExclude)) {
                    $excludeLocationCodes = explode(',', $locationsToExclude);
                    $locationCollection->addFieldToFilter('code', array('nin' => $excludeLocationCodes));

                }

                foreach ($locationCollection->getItems() as $location) {
                    $availableLocations[$location->getCode()] = $location;
                }

                break;
            default:
                $availableLocations = $customer->getAllowedLocations();
                break;
        }

        if ($locationCodes) {
            $locationCollection = $this->commResourceLocationCollectionFactory->create();
            /* @var $locationCollection \Epicor\Comm\Model\ResourceModel\Location\Collection */
            $locationCollection->addFieldToFilter('code', array('in' => $locationCodes));

            foreach ($locationCollection->getItems() as $location) {
                $availableLocations[$location->getCode()] = $location;
            }
        }

        return $availableLocations;
    }

    public function getCustomerDisplayLocationCodes()
    {
        return $this->getCustomerDisplayLocations(true);
    }

    public function getEscapedCustomerDisplayLocationCodes()
    {
        $locations = $this->getCustomerDisplayLocationCodes();

        $connection = $this->resourceConnection->getConnection('core_read');
        /* @var $connection Magento_Db_Adapter_Pdo_Mysql */

        $quotedLocations = array();
        foreach ($locations as $location) {
            $quotedLocations[] = $connection->quote($location);
        }

        if (empty($quotedLocations)) {
            $quotedLocations[] = "''";
        }

        return implode(',', $quotedLocations);
    }

    public function getCustomerDisplayLocations($codes = false)
    {
        /* @var $session \Magento\Customer\Model\Session */
        if (!$this->_displayLocations) {
            $session = $this->customerSessionFactory();
            if ($session->getDisplayLocations()) {
                $this->_displayLocations = $session->getDisplayLocations();
            } else {
                $this->_displayLocations = $this->getCustomerAllowedLocations();
            }
        }

        return $codes ? array_map('strval', array_keys($this->_displayLocations)) : $this->_displayLocations;
    }

    public function setCustomerDisplayLocationCodes($codes)
    {
        $displayLocations = array();
        $locations = $this->getCustomerAllowedLocations();
        if (is_array($locations)) {
            foreach ($locations as $location) {
                if (in_array($location->getCode(), $codes)) {
                    $displayLocations[$location->getCode()] = $location->getCode();
                }
            }
        }
        $this->setCustomerDisplayLocations($displayLocations);
        return $this;
    }

    public function setCustomerDisplayLocations($locations)
    {
        if (is_array($locations)) {
            $this->getCustomerSession()->setDisplayLocations($locations);
        }
        return $this;
    }

    /**
     * return a list of Locations in the preferred format with key as location_code
     * 
     * @return array
     */
    public function getAllLocations()
    {
        $locations = $this->commResourceLocationCollectionFactory->create()->getItems();
        $locationArray = array();
        foreach ($locations as $location) {
            /* @var $location Epicor_Comm_Model_Location */
            $locationArray[$location->getCode()] = $location;
        }
        return $locationArray;
    }

    /**
     * Optimizes locations selections based on provided locations array and pool of  other available locations
     * 
     * @param array $locations
     * @param array $pool
     * @param string $linkType
     * 
     * @return type
     */
    public function optimizeLocations($locations, $pool, $linkType)
    {
        $selectedCount = count($locations);
        $otherCount = count($pool);
        $threshold = 0;
       /* if ($selectedCount > $otherCount) {
            $threshold = ($selectedCount / ($selectedCount + $otherCount)) * 100;

            if ($threshold > 60) {
                $locations = $pool;
                $linkType = ($linkType == \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE) ? \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE : \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE;
            }
        }*/

        return array(
            'link_type' => $linkType,
            'locations' => $locations
        );
    }

    /**
     * ADMIN FUNCTIONS
     */

    /**
     * Gets a collection of customers that could be valid for a given location
     * 
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getCustomersCollectionForLocation($locationCode)
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */
        $collection->joinLocationLinkInfo($locationCode);
        $collection->addFieldToFilter('account_type', array('neq' => 'Supplier'));
        $include = \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE;
        $exclude = \Epicor\Comm\Model\Location\Link::LINK_TYPE_EXCLUDE;
        $collection->getSelect()->where('(link_type = "' . $include . '" OR (location_link_type = "' . $exclude . '" AND link_type IS NULL))');

        $customers = $this->customerResourceModelCustomerCollectionFactory->create()
            ->addNameToSelect()
            ->addAttributeToSelect('ecc_location_link_type');
        $customerErpLinkTable = $customers->getTable('ecc_customer_erp_account');
        $collection->setFlag('allow_duplicate', 1);
        $customers->joinTable(
            ['erp' => $customerErpLinkTable],
            'customer_id=entity_id',
            ['erp_link' => 'erp_account_id', 'erp_contact_code' => 'contact_code'],
            'erp.erp_account_id IN ('.implode(',', $collection->getAllIds()).')', 'inner'
        );
        return $customers;
    }

    /**
     * Processes an array of allowed customer against the given location code
     * 
     * @param string $locationCode
     * @param array $customers
     */
    public function syncCustomersToLocation($locationCode, $customers)
    {
        $collection = $this->getCustomersCollectionForLocation($locationCode);

        foreach ($collection->getItems() as $customer) {
            /* @var $customer Epicor_Comm_Model_Customer */
            $exclude = false;
            $include = false;
            if (!in_array($customer->getId(), $customers)) {
                // not selected, check it needs to be excluded
                if ($customer->isLocationAllowed($locationCode)) {
                    $exclude = true;
                }
            } else {
                if (!$customer->isLocationAllowed($locationCode)) {
                    $include = true;
                }
            }

            if ($exclude || $include) {
                if ($exclude) {
                    $customer->excludeLocation($locationCode);
                }

                if ($include) {
                    $customer->includeLocation($locationCode);
                }

                $customer->save();
            }
        }
    }

    public function syncProductsToLocation($locationCode, $products)
    {
        if (empty($products) || !is_array($products)) {
            $products[] = 0;
        }
        // Delete unlinked products
        // Get Product Locations where location = $locationCode & product_id not in $products
        // delete resulting collection items
        $productLocationCollection = $this->commResourceLocationProductCollectionFactory->create();
        /* @var $productLocationCollection Epicor_Comm_Model_Resource_Location_Product_Collection */
        $productLocationCollection
            ->addFieldToFilter('location_code', $locationCode)
            ->addFieldToFilter('product_id', array('nin' => $products));
        foreach ($productLocationCollection->getItems() as $productLocation) {
            /* @var $productLocation Epicor_Comm_Model_Location_Product */
            $productLocation->delete();
        }

        // Add newly linked products
        // Get Products joined with product locations where location = $locationCode
        // Diff between collection keys and $products. Create new blank entries for those products.
        $productCollection = $this->catalogResourceModelProductCollectionFactory->create();
        /* @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */

        //M1 > M2 Translation Begin (Rule 39)
        //$locationCode4Sql = $this->resourceConnection->getConnection('default_write')->quote($locationCode);
        $locationCode4Sql = $this->resourceConnection->getConnection()->quote($locationCode);
        //M1 > M2 Translation End

        $productCollection->getSelect()->joinInner(array('loc' => $productCollection->getTable('ecc_location_product')), 'loc.product_id=e.entity_id AND loc.location_code=' . $locationCode4Sql . '', array('*'), null, 'left');
        $productCollection->getSelect()->group('e.entity_id');
        $existingProductKeys = $productCollection->getAllIds();

        $newProducts = array_diff($products, $existingProductKeys);
        foreach ($newProducts as $productId) {
            if ($productId != 0) {
                $productLocation = $this->commLocationProductFactory->create();
                /* @var $productLocation Epicor_Comm_Model_Location_Product */
                $productLocation
                    ->setLocationCode($locationCode)
                    ->setProductId($productId)
                    ->save();
            }
        }
    }

    /**
     * Processes an array of allowed erp accounts against the given location code
     * 
     * @param string $locationCode
     * @param array $erpAccounts
     */
    public function syncErpAccountsToLocation($locationCode, $erpAccounts)
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */
        $collection->joinLocationLinkInfo($locationCode);
        $collection->addFieldToFilter('account_type', array('neq' => 'Supplier'));

        foreach ($collection->getItems() as $erpAccount) {
            /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
            $delete = false;
            $add = false;
            $linkType = $erpAccount->getLocationLinkType();
            if (!in_array($erpAccount->getId(), $erpAccounts)) {
                // not selected, check it needs to be excluded
                if ($linkType == \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE) {
                    // ERP account has inclusions
                    if ($erpAccount->getLinkType() == $linkType) {
                        // it was previously included, so need to remove it
                        $delete = true;
                    }
                } else {
                    // ERP account has exclusions
                    if ($erpAccount->getLinkType() != $linkType) {
                        // it's not previously been excluded, so need to add it
                        $add = true;
                    }
                }
            } else {
                // selected, check if it needs to be included
                if ($linkType == \Epicor\Comm\Model\Location\Link::LINK_TYPE_INCLUDE) {
                    // ERP account has inclusions
                    if ($erpAccount->getLinkType() != $linkType) {
                        // it's not previously included, so need to add it
                        $add = true;
                    }
                } else {
                    // ERP account has exclusions
                    if ($erpAccount->getLinkType() == $linkType) {
                        // it was previously excluded, so need to remove it
                        $delete = true;
                    }
                }
            }

            if ($add || $delete) {
                if ($add) {
                    $erpAccount->addLocationLink($locationCode, $linkType);
                }

                if ($delete) {
                    $erpAccount->deleteLocationLink($locationCode, $linkType);
                }

                $erpAccount->save();
            }
        }
    }

    /**
     * MESSAGE FUNCTIONS
     */

    /**
     * Checks a Location Exists, and if not creates it with the location code as data
     * @param $locationCode
     * @param null $company
     * @param array $stores
     * @param null $country
     * @return \Epicor\Comm\Model\Location|void
     * @throws \Exception
     */
    public function checkAndCreateLocation($locationCode, $company = null, $stores = [], $country = null)
    {
        if (!empty($locationCode)) {
            $location = $this->commLocationFactory->create();
            /* @var $location \Epicor\Comm\Model\Location */

            $location->load($locationCode, 'code');

            if ($location->isObjectNew()) {
                $location->setCode($locationCode);
                $location->setName($locationCode);
                $location->setCompany($company);
                $location->setDummy(1);
                $location->setSortOrder(0);

                if (empty($country)) {
                    $countryCode = $this->directoryHelper->getDefaultCountry();
                    $location->setCountry($countryCode);
                } else {
                    $location->setCountry($country);
                }
                // add it to all stores at the time
                $storeIds = [];
                if (empty($stores)) {
                    $collection = $this->storeGroup->create()->getCollection();
                    $stores = $collection->getItems();
                }

                foreach ($stores as $store) {
                    $store_company = $store->getWebsite()->getEccCompany() ?: $store->getGroup()->getEccCompany();
                    if (!is_null($company)
                    && $company != ""
                    && $company != $store_company) {
                        continue;
                    }
                    $storeIds[] = $store->getGroupId();
                }

                $location->setFullStores($storeIds);

                $location->save();
            }
            return $location;
        }
        return;
    }

    /**
     * Diff between to Location arrays or a location array 
     * and the full list of locations
     * The arrays are expected to have the location_codes as keys and 
     * the values as Location Models
     * 
     * @param array $sourceA
     * @param array $sourceB
     */
    public function getLocationDiff($sourceA, $sourceB = null)
    {
        if ($sourceA == null) {
            $sourceA = array();
        }
        if ($sourceB == null) {
            $sourceB = $this->getAllLocations();
        }
        return array_diff_key($sourceB, $sourceA);
    }

    public function getLocationDiffOptionArray($sourceA, $sourceB = null)
    {
        $options = array();
        foreach ($this->getLocationDiff($sourceA, $sourceB) as $location) {
            $options[] = array(
                'value' => $location->getCode(),
                'label' => $location->getName()
            );
        }
        return $options;
    }

    /**
     * OTHER, ARE THESE REALLY NEEDED?
     */

    /**
     * 
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSessionFactory();//$this->customerSession;
    }

    /**
     * Get Session Customer
     * 
     * @return \Epicor\Comm\Model\Customer
     */
    public function getCustomer()
    {
        return $this->getCustomerSession()->getCustomer();
    }

    public function getDefaultLocationCodeNoGlobal()
    {
        return $this->getDefaultLocationCode(false);
    }

    public function getDefaultLocationCode($useGlobal = true)
    {
        //check if customer default location set

        $session = $this->customerSessionFactory(); //$this->customerSession;
        /* @var $session Mage_Customer_Model_Session */
        $customer = $session->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        $defaultLocation = '';

        if ($session->isLoggedIn()) {
            $defaultLocation = $customer->getEccDefaultLocationCode();
            if (empty($defaultLocation) && $customer->getEccErpaccountId()) {
                $erpAccount = $this->getErpAccountInfo();
                $defaultLocation = $erpAccount->getDefaultLocationCode();
            }
        }

        if (empty($defaultLocation) && $useGlobal) {
            if ($session->isLoggedIn()) {
                if ($customer->getEccErpaccountId()) {
                    $erpAccount = $this->getErpAccountInfo();
                    $defaultType = $erpAccount->isTypeB2b() ? 'b2b' : 'b2c';
                } else {
                    $defaultType = 'b2c';
                }
            } else {
                $defaultType = 'guest';
            }

            $defaultLocation = $this->scopeConfig->getValue('epicor_comm_locations/global/' . $defaultType . 'default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $defaultLocation;
    }

    public function getProductAllowedLocations($product_id)
    {

        if (!isset($this->_productAllowedLocations[$product_id])) {
            $productLocations = $this->commResourceLocationProductCollectionFactory->create()->addFieldToFilter('product_id', array('eq' => $product_id));

            $productAllowedLocations = array();
            foreach ($productLocations as $productLocation) {
                $productAllowedLocations[] = $productLocation->getLocationCode();
            }

            $allowedCustomerLocations = $this->getCustomerAllowedLocationCodes();
            $this->_productAllowedLocations[$product_id] = array_intersect($allowedCustomerLocations, $productAllowedLocations);
        }

        return $this->_productAllowedLocations[$product_id];
    }

    public function isProductVisibleInDisplayedLocations($product_id)
    {
        $allowedLocations = $this->getProductAllowedLocations($product_id);
        $displayedLocations = $this->getCustomerDisplayLocationCodes();

        $intersection = array_intersect($allowedLocations, $displayedLocations);

        return (bool) (count($intersection) > 0);
    }

    public function getLocationsArray($product)
    {
        $locArray = $product->getCustomerLocations();
        $locData = array();
        foreach ($locArray as $locationCode => $location) {
            /* @var $location Epicor_Comm_Model_Location_Product */
            $locData[] = array(
                'code' => $locationCode,
                'name' => $location->getName()
            );
        }
        return $locData;
    }
    
    /**
     * Returns the stock visibility setting
     * 
     * @return string
     */
    public function getStockVisibilityFlag()
    {
        return $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Stock Visibility
     *
     * @return boolean
     */
    public function getAllsourceLocations()
    {
        return ($this->getScopeConfig()->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all_source_locations') ? true : false;
    }
    
    /**
     * Mapping Related Locations to a location
     * 
     * @param int $locationId
     * @param array $relatedlocations
     * @return void
     */
    public function syncRelatedLocations($locationId, $relatedlocations)
    {
        if (empty($relatedlocations) || !is_array($relatedlocations)) {
            $relatedlocations[] = 0;
        }
        
        // Deleting Unchecked Related Locations
        $delRelatedLocationCollection = $this->commResourceRelatedLocationCollectionFactory->create();
        $delRelatedLocationCollection->addFieldToFilter('location_id', $locationId)
                                    ->addFieldToFilter('related_location_id', array('nin' => $relatedlocations));
        $deleteItems = $delRelatedLocationCollection->getItems();
        foreach ($deleteItems as $relatedLocation) {
            $relatedLocation->delete();
        }

        // Add New Related Location
        $relatedLocationCollection = $this->commResourceRelatedLocationCollectionFactory->create();
        $relatedLocationCollection->addFieldToSelect('related_location_id')
                ->addFieldToFilter('location_id', $locationId);
        $items = $relatedLocationCollection->getData('related_location_id');
        $existingRelatedLocations = array_column($items, 'related_location_id');
        $newRelatedLocations = array_diff($relatedlocations, $existingRelatedLocations);
        foreach ($newRelatedLocations as $relatedLocationId) {
            if ($relatedLocationId != 0) {
                $relatedLocation = $this->commRelatedLocationFactory->create()
                                ->setLocationId($locationId)
                                ->setRelatedLocationId($relatedLocationId)
                                ->save();
            }
        }
        return;
    }
    
    /**
     * Mapping Locations to a Grouop
     * 
     * @param int $groupId
     * @param array $grouplocations
     * @param array $positions
     * @return void
     */
    public function syncGroupLocations($groupId, $grouplocations, $positions=array())
    {
        if (empty($grouplocations) || !is_array($grouplocations)) {
            $grouplocations[] = 0;
        }
        
        // Deleting Unchecked Group Locations
        $delGroupLocationCollection = $this->commResourceGrouplocationsCollectionFactory->create()
                                    ->addFieldToFilter('group_id', $groupId)
                                    ->addFieldToFilter('group_location_id', array('nin' => $grouplocations));
        $deleteItems = $delGroupLocationCollection->getItems();
        foreach ($deleteItems as $groupLocation) {
            $groupLocation->delete();
        }

        // Add New Group Location
        $groupLocationCollection = $this->commResourceGrouplocationsCollectionFactory->create()
                                    ->addFieldToFilter('group_id', $groupId);
        $items = $groupLocationCollection->getData('group_location_id');
        $existingGroupLocations = array_column($items, 'group_location_id');
        if (!empty($positions)) {
            foreach ($groupLocationCollection as $location) {
                $locationId = $location->getGroupLocationId();
                if (isset($positions[$locationId]['row_id']) && ($positions[$locationId]['row_id'] != "")) {
                    $location->setPosition($positions[$locationId]['row_id'])->save();
                }
            }
        }
        $newGroupLocations = array_diff($grouplocations, $existingGroupLocations);
        foreach ($newGroupLocations as $groupLocationId) {
            if ($groupLocationId != 0) {
                $groupLoc = $this->commGrouplocationFactory->create()
                                ->setGroupId($groupId)
                                ->setGroupLocationId($groupLocationId);
                if (isset($positions[$groupLocationId]['row_id']) && ($positions[$groupLocationId]['row_id'] != "")) {
                    $groupLoc->setPosition($positions[$groupLocationId]['row_id']);
                }        
                $groupLoc->save();
            }
        }
        return;
    }
    
    /**
     * Mapping groups to a location
     * 
     * @param int $locationId
     * @param array $relatedlocations
     * @return void
     */
    public function syncGroups($locationId, $groups)
    {
        if (empty($groups) || !is_array($groups)) {
            $groups[] = 0;
        }
        
        // Deleting Unchecked Related Locations
        $delGroupCollection = $this->commResourceGrouplocationsCollectionFactory->create();
        $delGroupCollection->addFieldToFilter('group_location_id', $locationId)
                           ->addFieldToFilter('group_id', array('nin' => $groups));
        $deleteItems = $delGroupCollection->getItems();
        foreach ($deleteItems as $group) {
            $group->delete();
        }

        // Add New Groups
        $groupCollection = $this->commResourceGrouplocationsCollectionFactory->create();
        $groupCollection->addFieldToSelect('group_id')
                ->addFieldToFilter('group_location_id', $locationId);
        $items = $groupCollection->getData('group_id');
        $existingGroups = array_column($items, 'group_id');
        $newGroups = array_diff($groups, $existingGroups);
        foreach ($newGroups as $groupId) {
            if ($groupId != 0) {
                $group = $this->commGrouplocationFactory->create()
                            ->setGroupLocationId($locationId)
                            ->setGroupId($groupId)
                            ->save();
            }
        }
        return;
    }
    
    /**
     *  Get Location Style 
     * 
     * @return boolean
     */
    public function getLocationStyle()
    {
        return $this->scopeConfig->getValue('epicor_comm_locations/global/style', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     *  Check if Location Picker is Enabled/Disabled
     * 
     * @return boolean
     */
    public function showLocationPicker()
    {
        return $this->scopeConfig->getValue('epicor_comm_locations/global/showlocationpicker', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     *  Check if Selected Location block is shown or not
     * 
     * @return boolean
     */
    public function showSelectedLocation()
    {
        return $this->scopeConfig->getValue('epicor_comm_locations/global/showselectedlocation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Display out of stock products option
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Gets the location form the given code
     * 
     * @param string $locationCode
     * 
     * @return \Epicor\Comm\Model\Location
     */
    public function getLocation($locationCode)
    {
        if (!isset($this->_locations[$locationCode])) {
            $location = $this->commLocationFactory->create();
            /* @var $location \Epicor\Comm\Model\Location */
            $location->load($locationCode, 'code');
            $this->_locations[$locationCode] = $location;
        } else {
            $location = $this->_locations[$locationCode];
        }
        return $location;
    }

    public function customerSessionFactory() {
        if (!$this->customerSessionFactoryExist) {
            $this->customerSessionFactoryExist = $this->customerSessionFactory->create();
        }
        return $this->customerSessionFactoryExist;
    }

    /**
     * make location not require for Configurable product
     * @return int
     */
    public function isLocationRequireForConfigurable()
    {
        return $this->scopeConfig->getValue('epicor_comm_locations/global/required_configurable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? 1 : 0;
    }

    /**
     * @param array $locations
     * @param \Epicor\Comm\Model\Product $product
     * @return array
     */
    public function filterMsqLocations($locations, $product)
    {
        return $this->locationFilter->filterMsqLocations($locations, $product);
    }

    /**
     * Check if product is out of stock under display out of stock
     *
     * @param \Epicor\Comm\Model\Product $product
     * @return bool
     */
    public function canShowOutOfStock($product)
    {
        $outOfStockNotAllowed = !$this->commHelper->isShowOutOfStock();
        if ($outOfStockNotAllowed) {
            $remove = $this->registry->registry('hide_out_of_stock_product');
            if($remove && in_array($product->getId(), $remove)){
                return false;
            }
        }
        return true;
    }
}
