<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model;


class Store extends \Magento\Store\Model\Store
{

    protected $_locations;
    protected $_allowedLocations;
    protected $_allowedLocationCodes;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Model\BrandingFactory
     */
    protected $commBrandingFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Link\CollectionFactory
     */
    protected $commResourceLocationLinkCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\ResourceModel\Store $resource,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Config\Model\ResourceModel\Config\Data $configDataResource,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\Information $information,
        $currencyInstalled,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\BrandingFactory $commBrandingFactory,
        \Epicor\Comm\Model\ResourceModel\Location\Link\CollectionFactory $commResourceLocationLinkCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $isCustomEntryPoint = false,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->commBrandingFactory = $commBrandingFactory;
        $this->commResourceLocationLinkCollectionFactory = $commResourceLocationLinkCollectionFactory;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $coreFileStorageDatabase,
            $configCacheType,
            $url,
            $request,
            $configDataResource,
            $filesystem,
            $config,
            $storeManager,
            $sidResolver,
            $httpContext,
            $session,
            $currencyFactory,
            $information,
            $currencyInstalled,
            $groupRepository,
            $websiteRepository,
            $resourceCollection,
            $isCustomEntryPoint,
            $data
        );
    }


    /**
     * Round price
     *
     * @param mixed $price
     * @return double
     */
    public function roundPrice($price, $precision = null)
    {
        $precision = $precision ?: $this->scopeConfig->getValue('Epicor_Comm/general/price_precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?: 4;
        return round($price, $precision);
    }

    /**
     * 
     * @param int $storeId
     * @return \Epicor\Comm\Model\Branding
     */
    public function getStoreBranding()
    {
        $branding = $this->commBrandingFactory->create();

        $store = $this->getGroup();
        $website = $this->getWebsite();

        $branding->setCompany($website->getCompany() ?: $store->getCompany());
        $branding->setSite($website->getSite() ?: $store->getSite());
        $branding->setWarehouse($website->getWarehouse() ?: $store->getWarehouse());
        $branding->setGroup($website->getGroup() ?: $store->getGroup());

        return $branding;
    }

    /**
     * 
     * @param int $storeId
     * @return \Epicor\Comm\Model\Branding
     */
    public function getCustomerTypes()
    {
        $allowedTypes = $this->getGroup()->getEccAllowedCustomerTypes();

        if (empty($allowedTypes)) {
            $allowedTypes = $this->getWebsite()->getEccAllowedCustomerTypes();
        }

        return $allowedTypes;
    }

    /**
     * Loads locations for this ERP Account
     * 
     * @return array
     */
    protected function _loadLocationLinks()
    {
        if (is_null($this->_locations)) {
            $this->_locations = array();
            $links = $this->commResourceLocationLinkCollectionFactory->create();
            /* @var $links Epicor_Comm_Model_Resource_Location_Link_Collection */
            $links->addFieldToFilter('entity_id', $this->getGroupId());
            $links->addFieldToFilter('entity_type', \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_STORE);


            foreach ($links as $link) {
                /* @var $link Epicor_Comm_Model_Location_Link */
                $this->_locations[] = $link->getLocationCode();
            }
        }

        return $this->_locations;
    }

    public function getAllowedLocationCodes()
    {
        return $this->getAllowedLocations(true);
    }

    /**
     * Get Allowed Locations
     * @param boolean $session
     * @return array
     */
    public function getAllowedLocations($codes = false)
    {
        if (is_null($this->_allowedLocations)) {
            $this->_allowedLocations = array();
            $this->_allowedLocationCodes = array();

            $locations = $this->_loadLocationLinks();
            sort($locations);

            $collection = $this->commResourceLocationCollectionFactory->create();
            /* @var $collection Epicor_Comm_Model_Resource_Location_Collection */

            $collection->addFieldToFilter('code', array('in' => $locations));
            $collection->setFlag('ignore_stores', true);

            foreach ($collection->getItems() as $location) {
                $this->_allowedLocations[$location->getCode()] = $location;
            }

            $this->_allowedLocationCodes = array_keys($this->_allowedLocations);
        }

        return ($codes) ? $this->_allowedLocationCodes : $this->_allowedLocations;
    }

}
