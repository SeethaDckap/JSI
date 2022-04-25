<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Message\Upload;

use Epicor\Comm\Service\AttributeCheck;
use Epicor\Comm\Service\AttributeOptions;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\Framework\App\ObjectManager;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link as GroupedProductLink;
use Epicor\Comm\Model\Product as CommProduct;
use Epicor\Comm\Model\Product\Type\Grouped as EpicorProductGrouped;
use Magento\GroupedProduct\Model\Product\Type\Grouped as MagentoProductGrouped;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use \Magento\BundleImportExport\Model\Export\RowCustomizer;
use Magento\Store\Model\ScopeInterface;

/**
 * Response STK - Upload Stock Record
 *
 * Send up information about a product, used to create/amend/delete
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Stknew extends \Epicor\Comm\Model\Message\Upload
{
    // Configuration path for the STK default Attribute set
    CONST XML_PATH_STK_DEFAULT_ATTR_SET = 'epicor_comm_field_mapping/stk_mapping/default_attribute_set';

    /**
     * @var \Epicor\Comm\Model\Product
     */
    protected $_startTime = 0;
    protected $_productEnityTypeId = 0;
    protected $_lastCheckPointTime = 0;
    protected $_exists = false;
    protected $_childExists = false;
    protected $_processingChildren = false;
    protected $_reindex = false;
    protected $_baseCurrencyIncluded = true;
    protected $_processingUOMs = false;
    protected $_flags;
    protected $_attributeSet = '';
    protected $_oldAttributeSet = '';
    protected $_stkType = '';
    protected $_productType = '';
    protected $_childType = '';
    protected $_taxClassId = '';
    protected $_websites = array();
    protected $_uomProducts = array();
    protected $_savedExcludedUom = array();
    protected $_validUoms = array();
    protected $_defaultUom = array();
    protected $_attributesToUpdate = array();
    protected $_attributesToUpdateStore = array();
    protected $_staticAttributesToUpdate = array();
    protected $_indexerModes = array();
    protected $_indexProducts = array();
    protected $_changes = false;
    protected $_storeId;
    protected $_maxDeadlockRetriesDefault = 5;
    protected $_entityTypeId = 5;
    protected $_existingProductAttributeSetName;
    protected $_selectValues = array();
    protected $_multiSelectValues = array();
    protected $_erpMappingAttributes = array();
    protected $_newAttribute = array();
    protected $_attributegroup = [];
    protected $_attributeId = [];
    protected $_visibilityOptions = [];
    protected $commProductHelperForStk = null;
    protected $_storesDefaultCurrency = array();
    protected $_productDataArray = array();
    protected $_productExitingDataArray = array();
    protected $_isConfigurable = false;
    protected $_processingConfigurable = false;
    private $_locations = [];
    private $_currencies;
    protected $_processNewattribute = false;
    protected $websitesCache = [];

    /**
     * All stores code-ID pairs.
     *
     * @var array
     */
    protected $storeIdToCode = [];

    /**
     * Reference Data
     */
    protected $_dropDownValues = array(
        '0' => 'Yes',
        '1' => 'No',
    );
    protected $_productTypes = array(
        'S' => 'simple',
        'C' => 'simple',
        'K' => 'simple',
        'E' => 'bundle'
    );

    protected $_productTypesName = array(
        'S' => 'Simple product',
        'C' => 'EWA configurator product',
        'K' => 'EWC configurator product',
        'E' => 'Extended kit (exploded parts)',
        'P' => 'Parts explosion'
    );
    protected $_productVisibility = array(
        'I' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
        'C' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
        'S' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
        'B' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
    );

    /**
     * Bundle product columns
     *
     * @var array
     */
    protected $bundleColumns = [
        RowCustomizer::BUNDLE_PRICE_TYPE_COL,
        RowCustomizer::BUNDLE_SKU_TYPE_COL,
        RowCustomizer::BUNDLE_PRICE_VIEW_COL,
        RowCustomizer::BUNDLE_WEIGHT_TYPE_COL,
        RowCustomizer::BUNDLE_VALUES_COL
    ];

    /**
     * Mapping for bundle types
     *
     * @var array
     */
    protected $bundleTypeMapping = [
        '0' => RowCustomizer::VALUE_DYNAMIC,
        '1' => RowCustomizer::VALUE_FIXED
    ];

    /**
     * Mapping for price views
     *
     * @var array
     */
    protected $bundlePriceViewMapping = [
        '0' => RowCustomizer::VALUE_PRICE_RANGE,
        '1' => RowCustomizer::VALUE_AS_LOW_AS
    ];

    /**
     * Mapping for price types
     *
     * @var array
     */
    protected $bundlePriceTypeMapping = [
        '0' => RowCustomizer::VALUE_FIXED,
        '1' => RowCustomizer::VALUE_PERCENT
    ];

    protected $_optionTypes = array(
        'T' => 'ecc_text_field',
        //'H' => 'ecc_text_hidden',
        //'S' => 'ecc_select_dropdown',
        //'M' => 'ecc_select_multiple',
        //'R' => 'ecc_select_radio',
        //'C' => 'ecc_select_checkbox'
    );
    protected $_updateConfMap = array(
        'name' => 'title',
        'short_description' => 'shortdescription',
        'ecc_default_category_position' => 'product_weighting_code',
        'ecc_brand' => 'supplier_brand',
        'ecc_brand_updated' => 'supplier_brand',
        'tax_class_id' => 'tax_code',
        'ecc_uom' => 'uom_sales_description',
        'price' => 'currencies',
        'ecc_google_feed' => 'google_feed',
        'ecc_manufacturers' => 'manufacturers',
        'ecc_manufacturers_ecc_data' => 'manufacturers',
        'ecc_related_documents' => 'related_documents',
        'ecc_pack_size' => 'pack_size',
        'ecc_related_documents_synced' => 'related_documents',
        'ecc_decimal_places' => 'decimal_places',
        'is_ecc_discontinued' => 'is_ecc_discontinued',
        'is_ecc_non_stock' => 'is_ecc_non_stock'
    );
    protected $_serialize = array(
        'ecc_erp_images',
        'ecc_previous_erp_images',
        'ecc_related_documents',
        'ecc_manufacturers_ecc_data'
    );

    /**
     * catalog  product Attribute Default setting option
     *
     * @var array
     */
    protected $_attributeDefaultValues = array(
        'separator' => '',
        'is_searchable' => false,
        'search_weight' => '1',
        'is_visible_in_advanced_search' => true,
        'is_comparable' => true,
        'is_filterable' => '0',
        'is_filterable_in_search' => false,
        'position' => '0',
        'is_used_for_promo_rules' => false,
        'is_html_allowed_on_front' => false,
        'is_visible_on_front' => false,
        'used_in_product_listing' => false,
        'used_for_sort_by' => false
    );

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $eavEntityAttributeSetFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Tax\Model\ClassModelFactory
     */
    protected $taxClassModelFactory;

    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $eavEntityFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $eavResourceModelEntityAttributeSetCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;
    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $eavConfigFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $eavEntityAttributeFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeStoreFactory;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Comm\Helper\ProductFactory
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\TableFactory
     */
    protected $eavEntityAttributeSourceTableFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $catalogResourceModelEavAttributeFactory;

    /**
     * @var \Epicor\Common\Model\XmlvarienFactory
     */
    protected $commonXmlvarienFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    private $_cacheState;

    /**
     * @var \Epicor\Comm\Model\GlobalConfig\Config
     */
    protected $globalConfig;
    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $eavEntityType;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $eavEntityAttributeSet;


    protected $frontendUrlHelper;

    /**
     * @var \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory
     */
    protected $taxCollectionFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Eav\Model\AttributeManagement
     */
    protected $attributeManagement;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $attributeOptionManagement;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_attributeLabelCache;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    protected $optionDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepositoryInterface;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ProductsFactory
     */
    protected $commErpMappingProductsFactory;

    /**
     * @var array
     */
    protected $mappedProductSkus = [];

    /**
     * @var array
     */
    protected $mappedProductUoms = [];

    private $productDefaultUomCode;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $configurableType;

    protected $productImportFactory;

    protected $catalogResourceModelProductFactoryExist = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogResourceModelProductFactory;


    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var Collection
     */
    protected $_optionColFactory;

    /**
     * @var AttributeOptions|null
     */
    private $attributeOptions;

    /**
     * @var AttributeCheck|null
     */
    private $attributeCheck;

    /**
     * Stknew constructor.
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $eavEntityAttributeSetFactory
     * @param \Epicor\Comm\Model\Context $context
     * @param \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxCollectionFactory
     * @param \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Tax\Model\ClassModelFactory $taxClassModelFactory
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $eavResourceModelEntityAttributeSetCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Epicor\Comm\Helper\Messaging $commMessagingHelper
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ConfigFactory $eavConfigFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $eavEntityAttributeFactory
     * @param \Magento\Store\Model\StoreFactory $storeStoreFactory
     * @param \Epicor\Comm\Helper\Locations $commLocationsHelper
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $eavEntityAttributeSourceTableFactory
     * @param \Magento\Framework\App\Cache\StateInterface $state
     * @param \Epicor\Comm\Model\GlobalConfig\Config $globalConfig
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $catalogResourceModelEavAttributeFactory
     * @param \Magento\Eav\Model\Entity\Type $eavEntityType
     * @param \Magento\Eav\Model\Entity\Attribute\Set $eavEntityAttributeSet
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Url $frontendUrlHelper
     * @param \Magento\Eav\Model\AttributeManagement $attributeManagement
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory
     * @param \Epicor\Comm\Model\Import\Product $productImportFactory
     * @param \Epicor\Comm\Model\Erp\Mapping\ProductsFactory $commErpMappingProductsFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param AttributeOptions|null $attributeOptions
     * @param AttributeCheck|null $attributeCheck
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Attribute\SetFactory $eavEntityAttributeSetFactory,
        \Epicor\Comm\Model\Context $context,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxCollectionFactory,
        \Epicor\Common\Model\XmlvarienFactory $commonXmlvarienFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Tax\Model\ClassModelFactory $taxClassModelFactory,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $eavResourceModelEntityAttributeSetCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ConfigFactory $eavConfigFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $eavEntityAttributeFactory,
        \Magento\Store\Model\StoreFactory $storeStoreFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $eavEntityAttributeSourceTableFactory,
        \Magento\Framework\App\Cache\StateInterface $state,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $catalogResourceModelEavAttributeFactory,
        \Magento\Eav\Model\Entity\Type $eavEntityType,
        \Magento\Eav\Model\Entity\Attribute\Set $eavEntityAttributeSet,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Url $frontendUrlHelper,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory,
        \Epicor\Comm\Model\Import\Product $productImportFactory,
        \Epicor\Comm\Model\Erp\Mapping\ProductsFactory $commErpMappingProductsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        AttributeOptions $attributeOptions = null,
        AttributeCheck $attributeCheck = null,
        array $data = []
    ) {
        $this->frontendUrlHelper = $frontendUrlHelper;
        $this->taxCollectionFactory = $taxCollectionFactory;
        $this->commonXmlvarienFactory = $commonXmlvarienFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->storeManager = $context->getStoreManager();
        $this->registry = $context->getRegistry();
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->eavEntityAttributeSetFactory = $eavEntityAttributeSetFactory;
        $this->logger = $context->getLogger();
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->taxClassModelFactory = $taxClassModelFactory;
        $this->eavEntityFactory = $eavEntityFactory;
        $this->eavResourceModelEntityAttributeSetCollectionFactory = $eavResourceModelEntityAttributeSetCollectionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->resourceConnection = $resourceConnection;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->eavConfig = $eavConfig;
        $this->eavConfigFactory = $eavConfigFactory;
        $this->eavEntityAttributeFactory = $eavEntityAttributeFactory;
        $this->storeStoreFactory = $storeStoreFactory;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->commProductHelper = $commProductHelper;
        $this->eavEntityAttributeSourceTableFactory = $eavEntityAttributeSourceTableFactory;
        $this->catalogResourceModelEavAttributeFactory = $catalogResourceModelEavAttributeFactory;
        $this->commProductHelper = $context->getCommProductHelper();
        $this->_cacheState = $state;
        $this->globalConfig = $globalConfig;
        $this->catalogResourceModelEavAttributeFactory = $catalogResourceModelEavAttributeFactory;
        $this->eavEntityType = $eavEntityType;
        $this->eavEntityAttributeSet = $eavEntityAttributeSet;
        $this->filterManager = $filterManager;
        $this->attributeManagement = $attributeManagement;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->productHelper = $productHelper;
        $this->optionDataFactory = $optionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_attributeLabelCache = $context->getCacheManager();
        $this->configurableType = $configurableType;
        $this->productImportFactory = $productImportFactory;
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;

        $this->commErpMappingProductsFactory = $commErpMappingProductsFactory->create();
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->_optionColFactory = $optionColFactory;

        $this->_storeId = $this->storeManager->getStore()->getId();

        //M1 > M2 Translation Begin (Rule p2-6.10)
        //Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_CODE);
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);

        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->setConfigBase('epicor_comm_field_mapping/stk_mapping/');
        $this->setMessageType('STK');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_PRODUCT);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_product', true, true);
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->attributeOptions = $attributeOptions ?: ObjectManager::getInstance()->get(AttributeOptions::class);
        $this->attributeCheck = $attributeCheck ?: ObjectManager::getInstance()->get(AttributeCheck::class);
    }

    /**
     * Updates product visibility direct to it's index
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product
     */

    public function catalogResourceModelProductFactory()
    {
        if (!$this->catalogResourceModelProductFactoryExist) {
            $this->catalogResourceModelProductFactoryExist = $this->catalogResourceModelProductFactory->create();
        }
        return $this->catalogResourceModelProductFactoryExist;
    }

    /**
     * Main Process function, called by message processing
     *
     * @return
     */
    public function processAction()
    {

        $adapter = $this->catalogProductFactory->create()->getResource();
        /* @var $adapter \Magento\Catalog\Model\ResourceModel\Product */
        $adapter->beginTransaction();
        $modeDelete = false;
        try {
            $productId = 0;
            $this->erpData = $this->getRequest()->getProduct();

            // gets data from message
            $this->_flags = $this->erpData->getData('_attributes');

            $this->_validateData();

            $sku = $this->erpData->getProductCode();
            $this->setMessageSubject($sku);
            $newCode = $this->isUpdateable('product_code_update') ? $this->erpData->getNewProductCode() : null;
            $loadSku = ($newCode == null) ? $sku : $newCode;

            $this->setMessageSubject($loadSku);
            if ($this->getDeadlockCount() == 0) {
                $this->_processCheckpoint('Start: ' . $loadSku);
            }

            // newCode condition required as renaming can come through as a delete.
            if ($this->_flags->getDelete() == 'Y' && ($newCode == null || $this->getConfigFlag('newproductcode_delete'))) {
                $this->setMessageSecondarySubject('Deleting ' . $loadSku);

                $neededColumns = [
                    'sku', 'ecc_uom_filter', 'attribute_set_id',
                    'ecc_configurator', 'ecc_oldskus', 'ecc_erp_images',
                    'ecc_configurable_part_price', 'ecc_lead_time',
                    'ecc_related_documents'
                ];
                $prodObj = $this->catalogProductFactory->create();
                $productId = $prodObj->getIdBySku($loadSku);
                $this->_exists = $productId ? true : false;
                if ($this->_exists) {
                    $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                    $this->_productExitingDataArray[$loadSku] = $this->catalogResourceModelProductFactory()->getAttributeRawValue($productId, $neededColumns, $storeId);
                }

                $this->_preStores();
                $this->_visibilityOptions = $this->catalogProductVisibility->getOptionArray();
                if ($this->_exists) {
                    $this->_productDataArray[$loadSku] = ['sku' => $loadSku];
                    $this->_productDataArray[$loadSku]['product_type'] = $this->_productExitingDataArray[$loadSku]['type_id'];
                }
                $this->_deleteProduct($loadSku);
                $productImport = $this->productImportFactory;
                if (!empty($this->_productDataArray)) {
                    $productImport->saveProductsData(
                        $this->_productDataArray
                    );
                }
                $modeDelete = true;
            } else {
                $oldsku = false;
                $updatSku = false;
                $prodObj = $this->catalogProductFactory->create();
                $productId = $prodObj->getIdBySku($loadSku);
                $this->_exists = $productId ? true : false;
                if ($newCode != null && !$this->_exists) {
                    /* @var $obj Epicor_Comm_Model_Product */
                    $obj = $this->catalogProductFactory->create();
                    $productId = $obj->getIdBySku($sku);
                    $this->_exists = $productId ? true : false;
                    if ($this->_exists) {
                        $this->setMessageSecondarySubject('Renaming ' . $sku . ' to ' . $newCode);
                        $updatSku = true;
                        $oldsku = $sku;
                    }
                } else {
                    $this->setMessageSecondarySubject('Updating ' . $loadSku);
                }

                if ($this->_exists && $updatSku) {
                    if ($newCode != null) {
                        $changes['sku'] = $newCode;
                        $where = ['entity_id=?' => $productId];
                        $connection = $this->resourceConnection->getConnection();
                        $table = $connection->getTableName('catalog_product_entity');
                        $this->updateData(
                            $table,
                            $changes,
                            $where
                        );
                        $this->_newAttribute['update_sku'] = true;
                    }
                }

                $this->_productDataArray[$loadSku] = ['sku' => $loadSku];
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                if ($this->_exists) {
                    $neededColumns = [
                        'sku', 'ecc_uom_filter', 'attribute_set_id',
                        'ecc_configurator', 'ecc_oldskus', 'ecc_erp_images',
                        'ecc_configurable_part_price', 'ecc_lead_time',
                        'ecc_related_documents'
                    ];
                    $this->_productExitingDataArray[$loadSku] = $this->catalogResourceModelProductFactory()->getAttributeRawValue($productId, $neededColumns, $storeId);
                    if ($oldsku) {
                        $oldskus = [];
                        if (isset($this->_productExitingDataArray[$loadSku]['ecc_oldskus']) &&
                            $this->_productExitingDataArray[$loadSku]['ecc_oldskus']) {
                            $oldskus = explode(',', $this->_productExitingDataArray[$loadSku]['ecc_oldskus']);
                        }
                        array_push($oldskus, $oldsku);
                        $oldskuText = implode(',', $oldskus);
                        $this->_setAttribute($loadSku, 'ecc_oldskus', $oldskuText);
                    }
                }
                if ($this->_exists) {
                    $this->_existingProductAttributeSetName = $this->eavEntityAttributeSetFactory->create()->load($this->_productExitingDataArray[$loadSku]['attribute_set_id'])->getAttributeSetName();
                }
                $this->_stkType = $this->_flags->getType();
                $this->_productType = $this->_productTypes[$this->_flags->getType()];

                if ($this->_stkType == 'E' && $this->isMappedSkuEon($loadSku)) {
                    $this->_stkType = 'S';
                    $this->_productType = $this->_productTypes[$this->_stkType];
                }
                $this->_processCheckpoint('Pre-Process');
                $this->_preProcessData($loadSku);

                if (count($this->_validUoms) > 1) {
                    $this->_childType = $this->_productType;
                    $this->_productType = 'grouped';
                }

                // if not create it
                if (!$this->_exists) {
                    $this->setMessageSecondarySubject('Creating ' . $loadSku);
                    $this->_processCheckpoint('Before Creation');
                    $product = $this->_setProductDefaults($loadSku);
                    // $product = $this->_createBaseProduct($loadSku);
                    $this->_processCheckpoint('After Creation');
                } else {
                    $this->_changeAttributeSet($loadSku);
                }

                $this->_processCheckpoint('Before Updating');
                // updates product attributes
                $this->_processProduct($loadSku, $this->erpData, $this->_productType);
                if ($this->_isConfigurable) {
                    $this->_processConfigurable($loadSku, $this->erpData);
                }
                $this->_productDataArray[$loadSku]['product_type'] = $this->_productDataArray[$loadSku]['ecc_stk_type'];

                foreach ($this->_websites as $websid) {
                    foreach ($this->_productDataArray as $sku => $data) {
                        if ($sku == 'delete_products') continue;
                        $this->websitesCache[$this->_productDataArray[$sku]['sku']][$websid] = true;
                    }
                }
                $start = microtime(true);
                $productImport = $this->productImportFactory;
                $finalProductData = [];
                if (count(array_keys($this->storeIdToCode)) > 1) {
                    foreach ($this->_attributesToUpdateStore as $storeid => $productDatas) {
                        foreach ($productDatas as $sku => $productData) {
                            $parentData = [];
                            $storedata = [];
                            if (isset($this->_productDataArray[$sku])) {
                                if (\Magento\Store\Model\Store::DEFAULT_STORE_ID == $storeid) {
                                    $parentData = $this->_productDataArray[$sku];
                                    $finalProductData[$sku] = array_merge($parentData, $productData);

                                    //Default Store should be disable and invisbale
                                    $productId = isset($this->_productExitingDataArray[$sku]['entity_id']) ?
                                        $this->_productExitingDataArray[$sku]['entity_id'] : 0;
                                    if ($productId && !isset($productData['visibility'])) {
                                        unset($finalProductData[$sku]['visibility']);
                                        unset($finalProductData[$sku]['status']);
                                    }
                                } else {
                                    $productData['_store'] = $this->storeIdToCode[$storeid];
                                    $productData['sku'] = strval($sku);
                                    $productData['product_type'] = $this->_productDataArray[$sku]['product_type'];
                                    $productData['_attribute_set'] = $this->_productDataArray[$sku]['_attribute_set'];
                                    $productData['attribute_set_code'] = $this->_productDataArray[$sku]['attribute_set_code'];
                                    $finalProductData[] = $productData;
                                }
                            }
                        }

                    }
                } else {
                    $finalProductData = $this->_productDataArray;
                }
                if (isset($this->_productDataArray['delete_products'])) {
                    $finalProductData['delete_products'] = $this->_productDataArray['delete_products'];
                }
                $this->_attributesToUpdateStore = [];
                $newProduct = $productImport->saveProductsData(
                    $finalProductData,
                    $this->websitesCache,
                    $this->_processNewattribute,
                    $this->_newAttribute
                );
                $end = microtime(true);
                $finalTime = $end - $start . ' seconds';
                //$this->logger->info($this->getLog()->getId() . ' ===  ' . $finalTime);

                $this->_processCheckpoint('After Updating');
            }
            $adapter->commit();
        } catch (\Exception $e) {
            if (!$modeDelete) {
                $adapter->rollback();
                $this->logger->debug($e);
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     * Is Mapped Sku
     * @param string $sku SKU.
     * @return boolen
     */

    private function isMappedSkuEon($sku): bool
    {
        if ($productUom = $this->getSkuSeparatedUomString($sku)) {
            return in_array($productUom, $this->mappedProductUoms);
        }

        return false;
    }

    /**
     * Returns a string of the sku and Uom separated by the UOM separator
     *
     * @param string $sku SKU.
     * @return string
     */
    private function getSkuSeparatedUomString($sku)
    {
        $skuSeparatedUomString = '';
        $uom = $this->getProductDefaultUomCode();
        $uomSeparator = $this->commMessagingHelper->create()->getUOMSeparator();
        if ($sku && $uom && $uomSeparator) {
            $skuSeparatedUomString = $sku . $uomSeparator . $uom;
        }

        return $skuSeparatedUomString;
    }

    /**
     * Returns a string of the Uom Default Flag Value
     *
     * @param string $unitOfMeasureItems UOM.
     * @return string
     */
    private function getUomDefaultFlagValue($unitOfMeasureItems)
    {
        foreach ($unitOfMeasureItems as $uomItem) {
            $itemAttributes = $uomItem->getData('_attributes');
            $itemCode = $uomItem->getCode();
            $defaultValue = $itemAttributes->getDefault();
            if ($defaultValue === 'Y') {
                return $itemCode;
            }
        }
    }

    /**
     * Returns a Product Default Uom code
     *
     * @return string
     */
    private function getProductDefaultUomCode()
    {
        if (!$this->productDefaultUomCode && $this->erpData) {
            $unitsOfMeasureGroup = $this->erpData->getUnitOfMeasures();
            $unitOfMeasureItems = $unitsOfMeasureGroup->getUnitOfMeasure();

            $this->productDefaultUomCode = $this->getDefaultUomCode($unitOfMeasureItems);
        }

        return $this->productDefaultUomCode;
    }

    /**
     * Returns a Default Uom code
     *
     * @return string
     */
    private function getDefaultUomCode($unitOfMeasureItems)
    {
        if (is_array($unitOfMeasureItems)) {
            return $this->getUomDefaultFlagValue($unitOfMeasureItems);
        } else {
            return $unitOfMeasureItems->getCode();
        }
    }


    /**
     * Changes Attribute Set if Updateable
     *
     * @param  $loadSku SKU.
     */
    protected function _changeAttributeSet($loadSku)
    {
        if (isset($this->_productExitingDataArray[$loadSku]['attribute_set_id'])) {
            $attSetId = $this->_productExitingDataArray[$loadSku]['attribute_set_id'];
            if ($this->isUpdateable('attributeset_update')) {
                $this->_productDataArray[$loadSku]['_attribute_set'] = $this->_attributeSetName;
                $this->_productDataArray[$loadSku]['attribute_set_code'] = $this->_attributeSetName;
                if (isset($this->_productExitingDataArray[$loadSku]['attribute_set_id']) &&
                    $this->_productExitingDataArray[$loadSku]['attribute_set_id'] != $this->_attributeSet) {
                    $this->_oldAttributeSet = $this->_productExitingDataArray[$loadSku]['attribute_set_id'];
                    $this->_newAttribute['new_attribute_set'][] = $this->_attributeSetName;
                }
            } else {
                $connection = $this->resourceConnection->getConnection();
                $attSeTtableName = $connection->getTableName('eav_attribute_set');
                $sql = $connection->select()
                    ->from($attSeTtableName, 'attribute_set_name')
                    ->where('attribute_set_id = ?', $attSetId);
                $attSetname = $connection->fetchOne($sql);
                $this->_productDataArray[$loadSku]['_attribute_set'] = $attSetname;
                $this->_productDataArray[$loadSku]['attribute_set_code'] = $attSetname;
            }
        } else {
            $this->_productDataArray[$loadSku]['_attribute_set'] = $this->_attributeSetName;
            $this->_productDataArray[$loadSku]['attribute_set_code'] = $this->_attributeSetName;
        }
    }

    /**
     * Resets STK flags depending on if it's a retry or not
     *
     * @param boolean $retry
     */
    public function resetProcessFlags()
    {
        if ($this->getDeadlockCount() > 0) {
            $this->_exists = false;
            $this->_childExists = false;
            $this->_processingChildren = false;
            $this->_baseCurrencyIncluded = true;
            $this->_processingUOMs = false;
            $this->_flags = null;
            $this->_attributeSet = '';
            $this->_attributeSetName = false;
            $this->_stkType = '';
            $this->_productType = '';
            $this->_childType = '';
            $this->_taxClassId = '';
            $this->_websites = array();
            $this->_uomProducts = array();
            $this->_savedExcludedUom = array();
            $this->_validUoms = array();
            $this->_defaultUom = array();
            $this->_attributesToUpdate = array();
            $this->_staticAttributesToUpdate = array();
            $this->_changes = false;
            $this->_indexProducts = array();
            $this->erpData = null;
            $this->_stores = null;
            $this->_isConfigurable = false;
            $this->_processingConfigurable = false;
        } else {
            $this->_startTime = microtime(true);
            $this->_lastCheckPointTime = microtime(true);
        }
    }

    /**
     * Validates data from ERP
     *
     * @throws Exception
     */
    protected function _validateData()
    {
        $productCode = $this->erpData->getProductCode();

        if ($productCode == "") {
            if ($this->erpData->hasData('product_code')) {
                throw new \Exception(
                    $this->getErrorDescription(self::STATUS_INVALID_PRODUCT_CODE, $productCode),
                    self::STATUS_INVALID_PRODUCT_CODE
                );
            } else {
                throw new \Exception(
                    $this->getErrorDescription(self::STATUS_XML_TAG_MISSING, 'productCode'),
                    self::STATUS_XML_TAG_MISSING
                );
            }
        }

        $validateLengthAttributeValues = $this->validateLengthAttributeValues();
        if (!empty($validateLengthAttributeValues)) {
            throw new \Exception(
                'Maximum length of attribute code must be less then 30', self::STATUS_GENERAL_ERROR
            );
        }

        $isValidCharacters = $this->validateAttributeValues();
        if (!empty($isValidCharacters)) {
            throw new \Exception(
                'Please use only letters (a-z), numbers (0-9) or underscore(_) in attribute code field, first character should be a letter.',
                self::STATUS_GENERAL_ERROR
            );
        }


        $validateUomAttributeLength = array_filter($this->validateUomAttributeLengthValues());
        if (!empty($validateUomAttributeLength)) {
            throw new \Exception(
                'Maximum length of UOM attribute code must be less then 30', self::STATUS_GENERAL_ERROR
            );
        }

        $validateUomAttributes = array_filter($this->validateUomAttributeValues());
        if (!empty($validateUomAttributes)) {
            throw new \Exception(
                'Please use only letters (a-z), numbers (0-9) or underscore(_) in UOM attribute code field, first character should be a letter.',
                self::STATUS_GENERAL_ERROR
            );
        }


        $newCode = $this->isUpdateable('product_code_update') ? $this->erpData->getNewProductCode() : null;
        if (!($this->_flags->getDelete() == 'Y' && $newCode == null)) {
            if (!isset($this->_productTypes[$this->_flags->getType()])) {
                throw new \Exception(
                    $this->getErrorDescription(self::STATUS_INVALID_TYPE, 'Product', $this->_flags->getType(), true),
                    self::STATUS_INVALID_TYPE
                );
            }

            $title = $this->erpData->getTitle();
            if (empty($title)) {
                if ($this->erpData->hasData('title')) {
                    throw new \Exception(
                        'Product Title is Blank', self::STATUS_GENERAL_ERROR
                    );
                } else {
                    throw new \Exception(
                        $this->getErrorDescription(self::STATUS_XML_TAG_MISSING, 'title'), self::STATUS_XML_TAG_MISSING
                    );
                }
            }
        }
    }

    /**
     * Validates UOM attribute code and check the format
     *
     * @return array
     */
    public function validateUomAttributeValues()
    {
        $erpData = $this->getRequest()->getProduct();
        $unitOfMeasures = $this->_getGroupedData('unit_of_measures', 'unit_of_measure', $erpData);
        $returnValues = array();
        foreach ($unitOfMeasures as $unitOfMeasure) {
            $uomsData[] = $unitOfMeasure;
        }
        if (!empty($uomsData)) {
            foreach ($uomsData as $child) {
                $returnValues[] = $this->checkUomDatas($child);
            }
        }
        return $returnValues;
    }

    public function checkUomDatas($child)
    {
        $attributes = $this->_getGroupedData('attributes', 'attribute', $child);
        $isValidCharacters = array();
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $code = strtolower($attribute->getCode());
                if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $code)) {
                    $isValidCharacters[] = $code;
                }
            }
        }
        if (isset($isValidCharacters[0])) {
            return $isValidCharacters[0];
        }
    }

    /**
     * Validates UOM attribute code length
     *
     * @return array
     */
    public function validateUomAttributeLengthValues()
    {
        $erpData = $this->getRequest()->getProduct();
        $unitOfMeasures = $this->_getGroupedData('unit_of_measures', 'unit_of_measure', $erpData);
        $returnValues = array();
        foreach ($unitOfMeasures as $unitOfMeasure) {
            $uomsData[] = $unitOfMeasure;
        }
        if (!empty($uomsData)) {
            foreach ($uomsData as $child) {
                $returnValues[] = $this->checkUomLengthDatas($child);
            }
        }
        return $returnValues;
    }

    /**
     * Validates UOM attribute code length of child
     *
     * @return array
     */

    public function checkUomLengthDatas($child)
    {
        $attributes = $this->_getGroupedData('attributes', 'attribute', $child);
        $isValidLength = array();
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $code = strtolower($attribute->getCode());
                if (strlen($code) > 30) {
                    $isValidLength[] = $code;
                }
            }
        }
        if (isset($isValidLength[0])) {
            return $isValidLength[0];
        }
    }

    /**
     * Validates attribute code and check the format
     *
     * @return array
     */
    public function validateAttributeValues()
    {
        $erpData = $this->getRequest()->getProduct();
        $attributes = $this->_getGroupedData('attributes', 'attribute', $erpData);
        $inValidCharacters = array();
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $code = strtolower($attribute->getCode());
                if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $code)) {
                    $inValidCharacters[] = $code;
                }
            }
            return $inValidCharacters;
        }
    }

    /**
     * Validates attribute code length
     *
     * @return array
     */
    public function validateLengthAttributeValues()
    {
        $erpData = $this->getRequest()->getProduct();
        $attributes = $this->_getGroupedData('attributes', 'attribute', $erpData);
        $inValidLength = array();
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                $code = strtolower($attribute->getCode());
                if (strlen($code) > 30) {
                    $inValidLength[] = $code;
                }
            }
            return $inValidLength;
        }
    }

    /*     * ********************** */
    /* Pre Process Functions */
    /*     * ********************** */

    /**
     * Data pre-processing, to save time procesing later
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _preProcessData($loadSku)
    {
        $this->_preStores();
        $this->_preProcessUoms($loadSku);
        $this->_preProcessTax($loadSku);
        $this->_preProcessWebsites($loadSku);
        $this->_preProcessAttributeSet($loadSku);
        $this->_preProcessAttributes($loadSku);
        $this->_preProcessStoresDefaultCurrencies($loadSku);
        $this->_preProcessErpMappingAttributes();
        $this->_visibilityOptions = $this->catalogProductVisibility->getOptionArray();
    }

    /**
     * Initialize stores hash.
     *
     * @return $this
     */
    protected function _preStores()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $this->storeIdToCode[$store->getId()] = $store->getCode();
        }
        return $this;
    }

    /*
     * Get All Erp Attribute mapping
     */
    protected function _preProcessErpMappingAttributes()
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('ecc_erp_mapping_attributes');
        $sql = $connection->select()
            ->from(['main_table' => $table], ['attribute_code', '*']);
        $this->_erpMappingAttributes = $connection->fetchAssoc($sql);
        $this->_erpMappingAttributes = array_change_key_case($this->_erpMappingAttributes, CASE_LOWER);
        return $this;
    }

    /*
     * Get All default Display currency of All Stores
     */
    protected function _preProcessStoresDefaultCurrencies()
    {
        $storesCurrencies = array();
        $allWebsites = $this->storeManager->getWebsites();
        foreach ($allWebsites as $website) {
            foreach ($website->getStores() as $store) {
                $code = $store->getDefaultCurrencyCode();
                if (!in_array($code, $storesCurrencies)) {
                    $storesCurrencies[] = $code;
                }
            }
        }
        if (!empty($storesCurrencies)) {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('ecc_erp_mapping_currency');
            $fields = array('erp_code');
            $sql = $connection->select()
                ->from($table, $fields)
                ->where('magento_id IN(?)', $storesCurrencies);
            $result = $connection->fetchCol($sql);
            if (count($result) > 0) {
                $this->_storesDefaultCurrency = $result;
            }
        }

    }

    /*
     * Get All Attribuet Datas
     */
    protected function _preProcessAttributes()
    {
        if (isset($this->_defaultUom['child']) && count($this->_validUoms) == 1) {
            $productAttributes = $this->_getGroupedData('attributes', 'attribute', $this->erpData);
            $uomAttributes = $this->_getGroupedData('attributes', 'attribute', $this->_defaultUom['child']);

            $attributes = array_merge($productAttributes, $uomAttributes);

            $erpDataAttributes = $this->erpData->getAttributes();
            if ($erpDataAttributes instanceof \Epicor\Common\Model\Xmlvarien) {
                $this->erpData->getAttributes()->setAttribute($attributes);
            } else {
                $erpDataAttributes = $this->commonXmlvarienFactory->create();
                $erpDataAttributes->setAttribute($attributes);
                $this->erpData->setAttributes($erpDataAttributes);
            }
        }
    }

    /**
     * Process Products Mapping
     *
     */
    protected function _productsMapping()
    {
        $productsMapping = $this->commErpMappingProductsFactory->getCollection()
            ->addFieldToSelect(['product_sku', 'product_uom'])
            ->getData();
        $uomSeparator = $this->commMessagingHelper->create()->getUOMSeparator();
        $this->mappedProductSkus = array_column($productsMapping, 'product_sku');
        foreach ($productsMapping as $productUoms) {
            $this->mappedProductUoms[] = $productUoms['product_sku'] . $uomSeparator . $productUoms['product_uom'];
        }
    }

    /**
     * Process UOM data into a useable array
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _preProcessUoms($loadSku)
    {
        $unitOfMeasures = $this->_getGroupedData('unit_of_measures', 'unit_of_measure', $this->erpData);
        // check if product already exists. If so, save UOM filter values as array
        if ($this->_exists && isset($this->_productExitingDataArray[$loadSku]['ecc_uom_filter'])) {
            if (is_string($this->_productExitingDataArray[$loadSku]['ecc_uom_filter'])) {
                $this->_savedExcludedUom = explode(',', $this->_productExitingDataArray[$loadSku]['ecc_uom_filter']);
            }
        }
        /* adding this check in as a bug found in Ironedge.
         * was unable to get ssh access do could not fully diagnose the root cause
         * Error was
         * Warning: explode() expects parameter 2 to be string, array given in /home/www/ironedge.com.au/src/app/code/community/Epicor/Comm/Model/Message/Upload/Stk.php on line 232
         *
         * 231 : $allExcludedUoms = array_merge(
         * 232 :          explode(',', $this->getConfig('unit_of_measure_filter')), explode(',', $this->_savedExcludedUom)
         * 233 : );
         */
        $configExcludedUoms = array();
        if (is_string($this->getConfig('unit_of_measure_filter'))) {
            $configExcludedUoms = explode(',', $this->getConfig('unit_of_measure_filter'));
        } elseif (is_array($this->getConfig('unit_of_measure_filter'))) {
            $configExcludedUoms = $this->getConfig('unit_of_measure_filter');
        }
        // end for ironedge bug fix

        if ($unitOfMeasures) {
            $allExcludedUoms = array_merge(
                $configExcludedUoms, $this->_savedExcludedUom
            );
            // reset actual uoms object with valid data
            $firstUOM = '';
            foreach ($unitOfMeasures as $unitOfMeasure) {
                $firstUOM = !$firstUOM ? $unitOfMeasure : $firstUOM;   // save first uom
                if (!in_array($unitOfMeasure->getCode(), $allExcludedUoms)) {
                    $this->_validUoms[] = $unitOfMeasure;
                    $atts = $unitOfMeasure->getData('_attributes') ?: $this->dataObjectFactory->create();

                    if ($atts->getDefault() == 'Y') {
                        $this->_defaultUom['code'] = $unitOfMeasure->getCode();
                        $this->_defaultUom['desc'] = $unitOfMeasure->getDescription();
                        $this->_defaultUom['decimal_places'] = $unitOfMeasure->getDecimalPlaces();
                        $this->_defaultUom['weight'] = $unitOfMeasure->getWeight();
                        $this->_defaultUom['child'] = $unitOfMeasure;
                    }
                }
            }
            if (!$this->_defaultUom) {
                $this->_defaultUom['code'] = $firstUOM->getCode();
                $this->_defaultUom['desc'] = $firstUOM->getDescription();
                $this->_defaultUom['decimal_places'] = $unitOfMeasure->getDecimalPlaces();
                $this->_defaultUom['child'] = $firstUOM;
            }
            $this->erpData->getUnitOfMeasures()->setUnitOfMeasure($this->_validUoms);            // reset erpData uom with valid data

            if ($this->_validUoms == 1) {

            }
        }
        $this->_processCheckpoint('ValidUOMsFiltered');
    }

    /**
     * Process Tax class data
     */
    protected function _preProcessTax()
    {

        if ($this->isUpdateable('tax_code_update', $this->_exists)) {

            $taxCode = $this->erpData->getTaxCode() ?: $this->getConfig('tax_code_default');

            if (empty($taxCode)) {
                throw new \Exception(
                    $this->getErrorDescription(self::STATUS_INVALID_TAX_CODE, 'Product', $taxCode),
                    self::STATUS_INVALID_TAX_CODE
                );
            }

            $taxClasses = $this->taxCollectionFactory->create()
                ->addFieldToFilter('class_name', $taxCode)
                ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT)
                ->addFieldToSelect('class_id')
                ->setPageSize(1);

            if (!empty($taxClasses) && (count($taxClasses) > 0)) {
                // parent code found in the mapping table
                $taxClass = $taxClasses
                    ->getFirstItem();
            } else {
                // create new tax class here
                $taxClass = $this->taxClassModelFactory->create();
                $taxClass->setClassName($taxCode);
                $taxClass->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT);
                $taxClass->save();
            }

            $this->_taxClassId = $taxClass->getId();
            $this->_processNewattribute['new_tax_class'] = true;
        }
    }

    /**
     * Process Tax class data
     */
    protected function _preProcessWebsites()
    {
        $visibleStores = $this->_loadStores();

        $websites = array();

        foreach ($visibleStores as $store) {
            $websites[] = $store->getWebsiteId();
        }

        $this->_websites = array_unique($websites);
    }

    /**
     * Process attribute set
     */
    protected function _preProcessAttributeSet($loadSku)
    {
        $this->entityTypeId = $this->eavEntityFactory->create()
            ->setType('catalog_product')
            ->getTypeId();
        // if no attribute set value supplied, and product exists, use current one, else use Default
        $attributeSetName = $this->erpData->getAttributeSet()
            ? $this->erpData->getAttributeSet()
            : ($this->_existingProductAttributeSetName
                ? $this->_existingProductAttributeSetName
                : $this->getDefaultAttributeSet());

        $this->_attributeSet = $this->eavResourceModelEntityAttributeSetCollectionFactory->create()
            ->setEntityTypeFilter($this->entityTypeId)
            ->addFieldToFilter('attribute_set_name', $attributeSetName)
            ->getFirstItem()
            ->getAttributeSetId();
        if (!$this->_attributeSet) {
            $this->_createNewAttributeSet($attributeSetName);
        }
        $this->_attributeSetName = $attributeSetName;
        $this->_changeAttributeSet($loadSku);
    }

    /**
     * @return mixed
     */
    private function getDefaultAttributeSet()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STK_DEFAULT_ATTR_SET,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Create Attribuet Set
     */
    protected function _createNewAttributeSet($attributeSet)
    {
        //retrieve default attribute set
        $defaultAttributeSetName = $this->scopeConfig->getValue('epicor_comm_field_mapping/stk_mapping/default_attribute_set',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //get id of default attribute set if not 'default'
        if ($defaultAttributeSetName == 'Default') {
            $defaultAttributeSetId = $this->catalogProductFactory->create()->getDefaultAttributeSetId();
        } else {
            $defaultAttributeSetId = $this->_getAttributeSetIdByName($defaultAttributeSetName);
        }

        //create new attribute set, cloning from the default
        //Mage::getModel('catalog/product_attribute_set_api')->create($attributeSet, $defaultAttributeSetId);
        $model = $this->eavEntityAttributeSetFactory->create();
        $model->setEntityTypeId($this->entityTypeId);

        $attributeSet = $this->filterManager->stripTags($attributeSet);
        $model->setAttributeSetName(trim($attributeSet));

        $model->save();
        $model->initFromSkeleton($defaultAttributeSetId);
        $model->save();

        // getid of new attribute set and set as current attribute set
        $newAttributeSetId = $this->_getAttributeSetIdByName($attributeSet);
        if (!$newAttributeSetId) {
            throw new \Exception("Attribute set {$attributeSet} does not exist and could not create. Cannot continue");
        }

        $this->_attributeSet = $newAttributeSetId;

        //set created by tag
        $newAttributeSet = $this->eavEntityAttributeSetFactory->create()->load($newAttributeSetId);
        $newAttributeSet->setEccCreatedBy('STK');
        $newAttributeSet->save();

        $this->_newAttribute['new_attribute_set'][] = $defaultAttributeSetId;
    }

    /**
     * Get Attribuet Set ID By name
     */
    protected function _getAttributeSetIdByName($name)
    {
        $attributeSetCollection = $this->eavResourceModelEntityAttributeSetCollectionFactory->create()
            ->setEntityTypeFilter($this->catalogProductFactory->create()->getResource()->getTypeId());

        foreach ($attributeSetCollection as $key => $value) {
            if ($value->getAttributeSetName() == $name) {
                return $key;
            }
        }
        return null;
    }

    /**
     * DELETION
     */

    /**
     * Deletes a product
     *
     * @throws \Exception
     */
    protected function _deleteProduct($product)
    {
        $deleted = false;

// checks if it exists and tries to delete it
        if ($this->isUpdateable('product_delete_update')) {
            if (is_object($product)) {
                $productId = $product->getId();
            } else {
                $productId = $this->catalogProductFactory->create()->getIdBySku($product);
                try {
                    $product = $this->_productRepositoryInterface->getById($productId);
                } catch (NoSuchEntityException $e) {
                    //skip sku not found
                    return $deleted;
                }
            }
            /* @var $product Epicor_Comm_Model_Product */
            if ($product->getId()) {
                try {
                    $notVisible = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE;
                    $disabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;

                    $deleteStores = $this->_loadStores();
                    $deleteStoresIds = $this->_storeIds;
                    $deleteWebsites = array();
                    $deleteWebsitesIds = array();
                    $allWebsites = $this->storeManager->getWebsites();
                    $delete = true;
                    $disableWebsites = array();

                    foreach ($deleteStores as $store) {
                        /* @var $store Mage_Core_Model_Store */
                        if (!in_array($store->getWebsiteId(), $deleteWebsites)) {
                            $deleteWebsites[$store->getWebsiteId()] = $store->getWebsite();
                            $deleteWebsitesIds[] = $store->getWebsiteId();
                        }
                    }
                    foreach ($allWebsites as $website) {
                        /* @var $website Mage_Core_Model_Website */
                        $disableWebsite = true;
                        $websiteProduct = $this->catalogProductFactory->create()->setStoreId($website->getDefaultStore()->getId())->load($productId);
                        /* @var $websiteProduct Epicor_Comm_Model_Product */
                        if ($websiteProduct->getStatus() != $disabled) {
                            foreach ($website->getStores() as $store) {
                                /* @var $store Mage_Core_Model_Store */
                                if (!in_array($store->getId(), $deleteStoresIds)) {
                                    $storeProduct = $this->catalogProductFactory->create()->setStoreId($store->getId())->load($productId);
                                    /* @var $storeProduct Epicor_Comm_Model_Product */
                                    if ($storeProduct->getVisibility() != $notVisible) {
                                        $delete = false;
                                        $disableWebsite = false;
                                        break;
                                    }
                                }
                            }
                            if ($disableWebsite && in_array($website->getId(), $deleteWebsitesIds)) {
                                $disableWebsites[] = $website;
                            }
                        }
                    }
                    $sku = $product->getSku();
                    if ($delete) {
                        $this->deleteGroupedProductChildIds($product);
                        $this->deleteConfigurableProduct($product->getSku());
                        $product->delete();
                        unset($this->_productDataArray[$sku]);
                        $deleted = true;
                    } else {
                        foreach ($disableWebsites as $website) {
                            /* @var $website Mage_Core_Model_Website */
                            $this->_setAttribute($sku, 'status', $disabled, $website->getDefaultStore()->getId());
                        }

                        foreach ($deleteStores as $store) {
                            /* @var $store Mage_Core_Model_Store */
                            $this->_setAttribute($sku, 'visibility', $this->_visibilityOptions[\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE], $store->getId());
                            $this->_productDataArray[$sku]['_store'] = $this->storeIdToCode[$store->getId()];
                            $this->updateVisibilityIndex($productId, $store->getId(), $notVisible);
                        }
                        $this->deleteProductLocations($product);
                        if ($product->getTypeId() == 'grouped') {
                            $linkedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                            foreach ($linkedProducts as $linkedProduct) {
                                foreach ($disableWebsites as $website) {
                                    /* @var $website Mage_Core_Model_Website */
                                    $this->_setAttribute($linkedProduct->getSku(), 'status', $disabled,
                                        $website->getDefaultStore()->getId());
                                }
                                foreach ($deleteStores as $store) {
                                    /* @var $store Mage_Core_Model_Store */
                                    $this->_setAttribute($linkedProduct->getSku(), 'visibility', $this->_visibilityOptions[\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE],
                                        $store->getId());
                                    $this->_productDataArray[$sku]['_store'] = $this->storeIdToCode[$store->getId()];
                                    $this->updateVisibilityIndex($linkedProduct->getId(), $store->getId(), $notVisible);
                                }
                                $this->deleteProductLocations($linkedProduct);
                            }
                        }
                        $this->_updateAttributes();
                    }
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage() . ' (' . $e->getMessage() . ')');
                }
            }
        }
        return $deleted;
    }

    /**
     * Deletes the location of product for given company
     *
     * @param $product
     */
    protected function deleteProductLocations($product)
    {
        $company = $this->getCompany();
        $currentLocations = $product->getLocationsWithoutExtra();
        if (is_array($currentLocations)) {
            foreach ($currentLocations as $location) {
                if ($company == $location->getCompany()) {
                    $location->delete();
                }
            }
        }
    }

    /**
     * Updates product visibility direct to it's index
     *
     * @param integer $productId
     * @param integer $storeId
     * @param integer $visibility
     */
    private function updateVisibilityIndex($productId, $storeId, $visibility, $exists = true)
    {
        if ($exists) {


            //M1 > M2 Translation Begin (Rule 39)
            //$resource = $this->resourceConnection;
            /* @var $resource Mage_Core_Model_Resource */
            //$write = $this->resourceConnection->getConnection('default_write');
            /* @var $write Zend_Db_Adapter_Abstract */
            $write = $this->resourceConnection->getConnection();
            //M1 > M2 Translation End
            $table = $write->getTableName('catalog_category_product_index');
            $update = array(
                'visibility' => $visibility
            );

            $where = array(
                'product_id = ' . $productId,
                'store_id = ' . $storeId
            );

            $write->update($table, $update, $where);
        }
    }

    private function deleteGroupedProductChildIds($product)
    {
        if ($product->getTypeId() === MagentoProductGrouped::TYPE_CODE && $this->isGroupedProductInstance($product)) {

            foreach ($this->getGroupedProductChildIds($product) as $productId) {
                /** @var CommProduct $productObject */
                $productObject = $this->_productRepositoryInterface->getById($productId);
                $this->_productRepositoryInterface->delete($productObject);
            }
        }
    }

    private function getGroupedProductChildIds($product): array
    {
        $ids = [];
        if ($id = $product->getId()) {
            $childIds = $product->getTypeInstance()->getChildrenIds($id);
            $ids = $childIds[GroupedProductLink::LINK_TYPE_GROUPED] ?? [];
        }

        return $ids;
    }

    private function isGroupedProductInstance($product): bool
    {
        $instanceType = $product->getTypeInstance();
        return $instanceType instanceof MagentoProductGrouped || $instanceType instanceof EpicorProductGrouped;
    }

    /**
     * Delete Config Product IF only one associated to them
     *
     * @param CommProduct $product
     * @throws \Exception
     */
    private function deleteConfigurableProduct($sku)
    {

        $typeId = $this->_productExitingDataArray[$sku]['type_id'];
        $productId = $this->_productExitingDataArray[$sku]['entity_id'];
        //get all parent's
        if ($typeId === "simple") {
            $parentIds = $this->configurableType->getParentIdsByChild($productId);
            if ($parentIds) {
                foreach ($parentIds as $oldParentId) {
                    $oldConfigProduct = null;
                    $childs = $this->configurableType->getChildrenIds($oldParentId);
                    if (isset($childs[0])) {
                        $childs = $childs[0];
                    }

                    //Only one associated to configurable then delete product itself
                    if (is_array($childs) && count($childs) <= 1 && array_key_exists($productId, $childs)) {
                        try {
                            $configProduct = $this->_productRepositoryInterface->getById($oldParentId);
                            /** @var CommProduct $configProduct */
                            $this->_productRepositoryInterface->delete($configProduct);
                        } catch (\Exception $e) {
                            throw new \Exception('something went wrong when delete configurable product:' . ' (' . $e->getMessage() . ')');
                        }
                    } else {
                        if (is_array($childs) && count($childs) > 0) {
                            $this->resetConfigurablePricingSku($childs, $oldParentId, $sku);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get Configurable Pricing Sku
     *
     * @param array $childs
     * @param integer $configurableProductId
     * @param string $sku
     * @throws \Exception
     */
    private function resetConfigurablePricingSku($childs = array(), $configurableProductId, $sku)
    {
        // Fetch price attribute id from eav_attribute table
        $attrPriceId = null;
        $connection = $this->resourceConnection->getConnection();
        $productId = $this->_productExitingDataArray[$sku]['entity_id'];
        $table = $connection->getTableName('eav_attribute');
        $field = array('attribute_id');
        $sql = $connection->select()
            ->from($table, $field)
            ->where('attribute_code = ?', 'price');
        $result = $connection->fetchCol($sql);
        if (is_array($result) && isset($result[0])) {
            $attrPriceId = $result[0];
            // fetch price of all Children
            $table = $connection->getTableName('catalog_product_entity_decimal');
            $fields = array('entity_id', 'value');
            $sql = $connection->select()
                ->from($table, $fields)
                ->where('attribute_id = ?', $attrPriceId)
                ->where('entity_id IN(?)', $childs)
                ->where('store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $result = $connection->fetchAll($sql);

            if (!empty($result) && count($result) > 0) {
                $priceList = array();
                foreach ($result as $key => $value) {
                    if ($productId != $value['entity_id']) {
                        $priceList[$value['entity_id']] = $value['value'];
                    }
                }

                $minProductPrice = min($priceList);
                $lowestPriceProductId = array_search($minProductPrice, $priceList);
                // find SKU of Product which has lowest Price
                $table = $connection->getTableName('catalog_product_entity');
                $field = array('sku');
                $sql = $connection->select()
                    ->from($table, $field)
                    ->where('entity_id = ?', $lowestPriceProductId);
                $resultLowestPriceSku = $connection->fetchCol($sql);
                if (is_array($resultLowestPriceSku) && isset($resultLowestPriceSku[0])) {
                    $resultLowestPriceSku = $resultLowestPriceSku[0];
                    $configProduct = $this->_productRepositoryInterface->getById($configurableProductId);
                    // Set New lowest price & Pricing Sku in configurable product
                    if ($configProduct->getEccPricingSku() != null && $configProduct->getEccPricingSku() == $sku) {
                        $configProduct->setEccPricingSku($resultLowestPriceSku);
                        $configProduct->setEccConfigurablePartPrice($minProductPrice);
                        $configProduct->save($configProduct);
                    } else {
                        if ($configProduct->getEccPricingSku() == null) {
                            // if Pricing Sku is null then set newly get Lowest price product sku
                            $configProduct->setEccPricingSku($resultLowestPriceSku);
                            $configProduct->setEccConfigurablePartPrice($minProductPrice);
                            $configProduct->save($configProduct);
                        }
                    }
                }
            }
        }
    }

    /**
     * Sets basic defaults for the product
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _setProductDefaults($sku)
    {
        // call various update things, like description, tax, stock etc
        $reorder = $this->_dropDownValues[0];                                                    //  no longer relevant to set to false if stkType = 'C'
        $this->_setAttribute($sku, 'ecc_reorderable', $reorder);

        //pradeep needs to implement
//        foreach ($product->getMediaAttributes() as $mediaAttribute) {
//            $mediaAttrCode = $mediaAttribute->getAttributeCode();
//            $this->_setAttribute($sku, $mediaAttrCode, 'no_selection');
//        }

        $this->_setAttribute($sku, 'ecc_configurator', 0);

        $this->_setAttribute($sku, 'visibility',
            $this->_visibilityOptions[\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);
        $this->_setAttribute($sku, 'status',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

    }


    /**
     * Creates a basic product with minimal data
     *
     * @param string $sku - SKU for the product
     * @param string $type - type (simple, bundle, grouped etc)
     *
     * @return \Epicor\Comm\Model\Product
     */
    protected
    function _setChildProductData($sku, $type, $stkType)
    {

        if ($this->isUpdateable('attributeset_update', $this->_exists())) {
            $this->_productDataArray[$sku]['_attribute_set'] = $this->_attributeSetName;
            $this->_productDataArray[$sku]['attribute_set_code'] = $this->_attributeSetName;
        }
        $this->_productDataArray[$sku]['type_id'] = $type;
        $this->_productDataArray[$sku]['sku'] = $sku;
        $this->_checkAndSetAttribute($sku, 'name', $this->erpData->getTitle() . $sku);
        $options = $this->_getGroupedData('options', 'option', $this->erpData);

        if (!empty($options) || $type == 'bundle' || $this->_stkType == 'C') {
            $this->_productDataArray[$sku]['has_options'] = 1;
        }

        if ($type == 'bundle' || $this->_stkType == 'E') {
            $weightType = $this->getConfigFlag('kit_weight_fixed') ? 1 : 0;
            $this->_productDataArray[$sku]['weight_type'] = $weightType;
            $this->_productDataArray[$sku]['sku_type'] = 1;
            $this->_productDataArray[$sku]['price_view'] = 0;
            $this->_productDataArray[$sku]['price_type'] = 1;
            //pradeep needs to implement
            //$this->_processBundleOptions($this->erpData, $product);
        }

        //configurable product
        if ($type == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            if ($this->erpData->getParent()->getTitle()) {
                $this->_checkAndSetAttribute($sku, 'name', $this->erpData->getParent()->getTitle());
            }
        }


        //   $this->_indexProducts[] = $product->getId();
        $this->_setProductDefaults($sku);
        return $sku;
    }


    /**
     * Sets the data for a location
     *
     * @param integer $productId
     * @return Link\Products|mixed
     */
    public function getLinkProducts($productId)
    {
        if ($productId) {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('catalog_product_link');
            $sql = $connection->select()
                ->from(['main_table' => $table], [])
                ->joinLeft(
                    array(
                        'cpe' => $connection->getTableName('catalog_product_entity')
                    ), 'cpe.entity_id = main_table.linked_product_id', array(
                        'sku'
                    )
                )
                ->where('main_table.product_id = ?', $productId);
        }
        return $connection->fetchAssoc($sql);

    }

    /**
     * Processes the creation / update of child uom products
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $children
     */
    protected function _processUomChildren($loadSku, $children)
    {
        $uomSeparator = $this->commMessagingHelper->create()->getUOMSeparator();
        $oldsku = false;
        if (isset($this->_newAttribute['update_sku'])) {
            $productCode = $this->erpData->getProductCode() . $uomSeparator;
            $newproductCode = $this->erpData->getNewProductCode() . $uomSeparator;
            $oldsku = $productCode;
        } else {
            $productCode = $loadSku . $uomSeparator;
        }
        $linkProducts = array();
        $prices = array();

        $this->_processingChildren = true;
        $exitingChildProducts = [];
        if ($this->_exists) {
            $parentProductId = isset($this->_productExitingDataArray[$loadSku]['entity_id']) ?
                $this->_productExitingDataArray[$loadSku]['entity_id'] : 0;
            if ($parentProductId) {
                $exitingChildProducts = $this->getLinkProducts($parentProductId);
            }
        }
        // See note below on singleton as to why we store this data
        $origUOM = $this->erpData->getUomSalesDescription();
        $origCurrencies = $this->erpData->getCurrencies();
        $origAttribute = $this->erpData->getAttributes();
        $origWeight = $this->erpData->getWeight();
        $origDecimalPlaces = $this->erpData->getDecimalPlaces();
        $origPackSize = $this->erpData->getPackSize();

        foreach ($children as $child) {
            $sku = $productCode . $child->getCode();
            $updateoldsku = false;
            if (isset($exitingChildProducts[$sku])) {
                unset($exitingChildProducts[$sku]);
            }
            $productId = $this->catalogProductFactory->create()->getIdBySku($sku);

            if (!$productId) {
                $this->_childExists = false;
            } else {
                $neededColumns = [
                    'sku', 'ecc_uom_filter', 'attribute_set_id',
                    'ecc_configurator', 'ecc_oldskus', 'ecc_erp_images',
                    'ecc_configurable_part_price', 'ecc_lead_time',
                    'ecc_related_documents'
                ];
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                $this->_productExitingDataArray[$sku] = $this->catalogResourceModelProductFactory()->getAttributeRawValue($productId, $neededColumns, $storeId);
                $this->_childExists = true;
                if (isset($this->_newAttribute['update_sku'])) {
                    $newCode = $newproductCode . $child->getCode();
                    $updateoldsku = $oldsku . $child->getCode();
                    $changes['sku'] = $newCode;
                    $where = ['entity_id=?' => $productId];
                    $connection = $this->resourceConnection->getConnection();
                    $table = $connection->getTableName('catalog_product_entity');
                    $this->updateData(
                        $table,
                        $changes,
                        $where
                    );
                    unset($this->_productExitingDataArray[$sku]);
                    $sku = $newCode;
                    $this->_newAttribute['update_sku'] = true;
                    $neededColumns = [
                        'sku', 'ecc_uom_filter', 'attribute_set_id',
                        'ecc_configurator', 'ecc_oldskus', 'ecc_erp_images',
                        'ecc_configurable_part_price', 'ecc_lead_time',
                        'ecc_related_documents'
                    ];
                    $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                    $this->_productExitingDataArray[$sku] = $this->catalogResourceModelProductFactory()->getAttributeRawValue($productId, $neededColumns, $storeId);
                    $this->_childExists = true;

                    if ($updateoldsku) {
                        $oldskus = [];
                        if (isset($this->_productExitingDataArray[$sku]['ecc_oldskus']) &&
                            $this->_productExitingDataArray[$sku]['ecc_oldskus']) {
                            $oldskus = explode(',', $this->_productExitingDataArray[$sku]['ecc_oldskus']);
                        }
                        array_push($oldskus, $updateoldsku);
                        $oldskuText = implode(',', $oldskus);
                        $this->_setAttribute($sku, 'ecc_oldskus', $oldskuText);
                    }
                }
            }
            $this->_setChildProductData($sku, $this->_childType, $this->_stkType);
            $_childAttributes = $child->getAttributes();
            if (!is_null($_childAttributes) && !is_null($origAttribute)) {
                $origAttrCount = count(array($origAttribute->getAttribute()));
                $childAttrCount = count(array($_childAttributes->getAttribute()));
                if ($origAttrCount > $childAttrCount) {
                    $childAttrData = $origAttrData = [];
                    $childAttrData = $this->getAttributeData($_childAttributes);
                    $origAttrData = $this->getAttributeData($origAttribute);
                    $diffData = array_diff_key($origAttrData, $childAttrData);
                    $childAttrData = array_merge($childAttrData, $diffData);
                    $_childAttributes = $this->commonXmlvarienFactory->create();
                    $_childAttributes->setAttribute($childAttrData);
                }

            } else {
                if (!is_null($origAttribute)) {
                    $_childAttributes = $origAttribute;
                }
            }

            $this->_setAttribute($sku, 'visibility',
                $this->_visibilityOptions[\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]);
            if (in_array($sku, $this->mappedProductSkus) || in_array($sku, $this->mappedProductUoms)) {
                $this->_setAttribute($sku, 'visibility',
                    $this->_visibilityOptions[\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH]);
            }
            $uomErpData = $this->erpData;
            $uomErpData->setUomSalesDescription($child->getCode());
            $uomErpData->setCurrencies($child->getCurrencies());
            $uomErpData->setAttributes($_childAttributes);
            $uomErpData->setWeight($child->getWeight());
            $uomErpData->setDecimalPlaces($child->getDecimalPlaces());
            $uomErpData->setPackSize($child->getDescription());
            //$this->_productDataArray[$sku]['url_key'] = $this->_productDataArray[$sku]['name'] . $child->getCode();
            $this->_processProduct($sku, $uomErpData, $this->_childType);

            //$this->_processAttributes($child, $sku);
            $this->_uomProducts[$sku] = $sku;
            $linkProducts[$sku] = array('position' => 0, 'qty' => '');
            $prices[$sku] = isset($this->_productDataArray[$sku]['price']) ? $this->_productDataArray[$sku]['price'] : 0;
        }

        $this->_processingChildren = false;

        $updateUomOrder = $this->isUpdateable('uom_order_update', $this->_exists);
        $linkProducts = [];
        if ($updateUomOrder) {
            asort($prices);
            $pos = 0;
            foreach ($prices as $id => $price) {
                $linkProducts[$pos] = $id . '=0';

                $pos++;
            }
        } else {
            asort($prices);
            foreach ($prices as $id => $price) {
                $linkProducts[] = $id . '=0';
            }
        }

// have to do this otherwise due to the singleton nature of magento, the parent will get the last childs info :(

        $this->erpData->setUomSalesDescription($origUOM);
        $this->erpData->setCurrencies($origCurrencies);
        $this->erpData->setAttributes($origAttribute);
        $this->erpData->setWeight($origWeight);
        $this->erpData->setDecimalPlaces($origDecimalPlaces);
        $this->erpData->setPackSize($origPackSize);

        $this->_productDataArray[$loadSku]['associated_skus'] = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $linkProducts);
        if (!empty($exitingChildProducts)) {
            foreach ($exitingChildProducts as $sku => $data) {
                $this->_productDataArray['delete_products'][$sku] = ['sku' => $sku];
            }
        }
    }

    /**
     * @param $data
     * @return array
     */
    protected function getAttributeData($data)
    {
        $attrData = [];
        $dataCount = $data->getAttribute();
        if (count($dataCount) == 1) {
            $code = $data->getAttribute()->getData('code');
            $attrData[$code] = $data->getAttribute();
        } else {
            foreach ($data->getAttribute() as $_data) {
                $code = $_data->getData('code');
                $attrData[$code] = $_data;
            }
        }
        return $attrData;
    }

    /**
     * CORE
     */

    /**
     * Runs the main data processing functions, and type specific ones
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param string $type
     */
    protected function _processProduct($loadSku, $erpData, $type)
    {
        // checks if the product exists
        $this->_processTypeChanges($loadSku, $type);
        if ($type == 'grouped') {
            $this->_processGrouped($loadSku, $erpData);
        } else {
            switch ($this->_stkType) {
                case 'S': //Simple
                    $this->_processSimple($loadSku, $erpData);
                    $this->_processType($loadSku, $erpData, $this->_stkType);
                    break;
                case 'E': // Exploded Kit
                    if ($this->isMappedSkuEon($loadSku)) {
                        $this->_processSimple($loadSku, $erpData);
                    } else {
                        $this->_processBundle($loadSku, $erpData);
                    }
                    $this->_processType($loadSku, $erpData, $this->_stkType);
                    break;
                case 'C': // Configurator
                    $this->_processConfigurator($loadSku, $erpData);
                    $this->_processType($loadSku, $erpData, $this->_stkType);
                    break;
                case 'K': // Configurator
                    $this->_processConfigurator($loadSku, $erpData);
                    $this->_processType($loadSku, $erpData, $this->_stkType);
                    break;
            }
        }

        $this->_processData($loadSku, $erpData);
        $this->_updateAttributes();
    }

    /**
     * Kinetic Configurator product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processType($loadSku, $erpData, $type)
    {
        //set is configurable
        if ($type == "S" &&
            $this->_productType == "simple" &&
            $erpData->getParent() &&
            $erpData->getParent()->getProductCode()
        ) {
            $this->_isConfigurable = true;
        }

        $this->_productDataArray[$loadSku]['product_type'] = $this->_productTypes[$type];
        $value = $this->_productTypesName[$type];
        $this->_setAttribute($loadSku, 'ecc_product_type', $value);
    }

    /**
     * Does code necessary for when the product type changes
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param string $type
     */
    public function _processTypeChanges($loadSku, $type)
    {
        if ($this->_exists) {
            if ($this->_exists() && $type != $this->_productExitingDataArray[$loadSku]['type_id']) {
                $this->_changeProductType($loadSku, $type);
            }
            $isConfigurator = isset($this->_productExitingDataArray[$loadSku]['ecc_configurator']) ? $this->_productExitingDataArray[$loadSku]['ecc_configurator'] : false;
            if (($this->_stkType != 'K' && $this->_stkType != 'C') && $isConfigurator) {
                $this->_removeConfiguratorOption($loadSku);
            }

            if (($this->_stkType != 'K' || $this->_stkType != 'C') && $isConfigurator) {
                $this->_setAttribute($loadSku, 'ecc_configurator', 0);
                $this->_setAttribute($loadSku, 'ecc_reorderable', $this->_dropDownValues[0]);
            }
        }
        $this->_productDataArray[$loadSku]['product_type'] = $type;
    }

    /**
     * UPDATING - TYPE SPECIFIC
     */

    /**
     * Simple product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processSimple($loadSku, $erpData)
    {
        // not needed at the moment, but may be in future
    }

    /**
     * Grouped product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processGrouped($loadSku, $erpData)
    {
        if (count($this->_validUoms) > 1) {
            $this->_processUomChildren($loadSku, $this->_validUoms);
        }
    }

    /**
     * Bundle product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processBundle($loadSku, $erpData)
    {
        $this->_processBundleOptions($erpData, $loadSku);
    }

    /**
     * Configurator product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processConfigurator($loadSku, $erpData)
    {
        $this->_setAttribute($loadSku, 'ecc_configurator', 1);
        $this->_processConfiguratorOption($loadSku);
    }

    /**
     * Processes the data from the message and builds updates as needed
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processData($loadSku, $erpData)
    {

        $type = $this->_processingChildren ? $this->_childType : $this->_productType;
        $this->_setAttribute($loadSku, 'ecc_stk_type', $type);

        $this->_productDataArray[$loadSku]['ecc_stk_type'] = $type;
        $newCode = $erpData->getNewProductCode();

        $currencies = $this->_getCurrencies($erpData);

        $this->_checkAndSetAttribute($loadSku, 'name', $erpData->getTitle());
        $this->_checkAndSetAttribute($loadSku, 'short_description', $erpData->getShortDescription());
        $this->_checkAndSetAttribute($loadSku, 'description', $erpData->getDescription());
        $this->_checkAndSetAttribute($loadSku, 'ecc_default_category_position', $erpData->getProductWeightingCode());

        $googleFeed = $this->_flags->getGoogle();
        $showInGoogle = (strtoupper($googleFeed) === 'Y') ? 1 : 0;
        $this->_checkAndSetAttribute($loadSku, 'ecc_google_feed', $showInGoogle);
        $sb = $erpData->getSupplierBrand();
        $this->_checkAndSetEccBrand($loadSku, 'ecc_brand', $sb);
        $this->_checkAndSetEccBrandD($loadSku, $sb);
        $this->_checkAndSetAttribute($loadSku, 'ecc_manufacturers_ecc_data', $this->_getManufacturers($erpData));
        $this->_setAttribute($loadSku, 'ecc_lead_time', $this->_getLeadTime($erpData, $loadSku));
        $this->_checkAndSetAttribute($loadSku, 'weight', $this->_getWeight($erpData));
        $this->_checkAndSetAttribute($loadSku, 'tax_class_id', $this->_taxClassId);
        $this->_checkAndSetAttribute($loadSku, 'hazard_class', $erpData->getData('hazard_class'));
        $this->_checkAndSetAttribute($loadSku, 'hazard_class_desc', $erpData->getData('hazard_class_desc'));
        $this->_checkAndSetAttribute($loadSku, 'hazard_code', $erpData->getData('hazard_code'));
        $this->_checkAndSetAttribute($loadSku, 'id_number', $erpData->getData('id_number'));
        $this->_checkAndSetAttribute($loadSku, 'supplierpartnumber', $erpData->getData('supplier_part_number'));

        //M1 > M2 Translation Begin (Rule p2-6.6)
        //$baseCurrency = Mage::app()->getBaseCurrencyCode();
        $baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();

        $basePrice = isset($currencies[$baseCurrency]['base_price']) ? $currencies[$baseCurrency]['base_price'] : 0;
        $costPrice = isset($currencies[$baseCurrency]['cost_price']) ? $currencies[$baseCurrency]['cost_price'] : 0;

        $this->_checkAndSetAttribute($loadSku, 'price', $basePrice);
        $this->_checkAndSetAttribute($loadSku, 'cost', $costPrice);

        //Discontinued & Non Stock Item WSO-7913 & WSO-7913
        $discontinued = $erpData->getData('is_discontinued') == "Y" ? true : false;
        $nonStock = $erpData->getData('is_non_stock') == "Y" ? true : false;
        $this->_checkAndSetAttribute($loadSku, 'is_ecc_discontinued', $discontinued);
        $this->_checkAndSetAttribute($loadSku, 'is_ecc_non_stock', $nonStock);

        if ($this->_processingChildren) {
            // have to set this otherwise UOM ordering gets screwed, product is not saved so it's ok here
            //$product->setPrice($basePrice);
            $this->_checkAndSetAttribute($loadSku, 'price', $basePrice);
        }
        $relatedDocvalue = $this->_getRelatedDocuments($erpData, $loadSku);
        $this->_checkAndSetAttribute($loadSku, 'ecc_related_documents', $relatedDocvalue);
        $this->_processUom($erpData, $loadSku);
        $this->_processCustomOptions($erpData, $loadSku);
        $this->_processErpImages($erpData, $loadSku);
        $this->_processStock($erpData, $loadSku);
        $this->_processAttributes($erpData, $loadSku);
        $this->_processVisibility($erpData, $loadSku, $currencies);
        $this->_processLocations($erpData, $loadSku);
        $this->_processMetaInformation($erpData, $loadSku);
        if ($this->_cacheState->isEnabled('message')) {
            if (isset($this->_productExitingDataArray[$loadSku]['sku'])) {
                $this->_cacheManager->clean($this->_productExitingDataArray[$loadSku]['sku']);
            }
        }
    }

    /**
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function _processConfigurable($loadSku, $erpData)
    {
        $this->_validateConfiguration();
        $erpConfigurableData = $erpData->getParent();
        $configurableSKU = $erpConfigurableData->getProductCode();
        if ($configurableSKU) {
            /* @var $obj Epicor_Comm_Model_Product */
            $obj = $this->catalogProductFactory->create();
            $productId = $obj->getIdBySku($configurableSKU);
            $isnew = true;
            if ($productId) {
                $isnew = false;
                $neededColumns = [
                    'sku', 'ecc_uom_filter', 'attribute_set_id',
                    'ecc_configurator', 'ecc_oldskus', 'ecc_erp_images',
                    'ecc_configurable_part_price', 'ecc_lead_time',
                    'ecc_related_documents'
                ];
                $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                $this->_productExitingDataArray[$configurableSKU] = $this->catalogResourceModelProductFactory()->getAttributeRawValue($productId, $neededColumns, $storeId);
            }

            $this->_processingConfigurable = true;
            $configProductSku = $this->_setChildProductData($configurableSKU,
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE, $this->_stkType);
            $this->_productDataArray[$configurableSKU]['product_type'] = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
            $this->_processConfigurableData($loadSku, $configProductSku, $erpData, $isnew);
            $this->_processConfigurableOption($loadSku, $configurableSKU, $erpData);
            $this->_updateAttributes();
        }
    }

    /**
     * @throws \Exception
     */
    protected function _validateConfiguration()
    {
        $parentErpData = $this->erpData->getParent();
        if ($parentErpData) {
            $productCode = $parentErpData->getProductCode();
            if (empty($productCode)) {
                if ($parentErpData->hasData('product_code')) {
                    throw new \Exception(
                        'Parent productCode is Blank', self::STATUS_GENERAL_ERROR
                    );
                }
            }
            $attributeSet = $parentErpData->getAttributeSet();
            if ($attributeSet && $attributeSet != $this->_attributeSet) {
                throw new \Exception(
                    'Parent attributeSet not match with child product', self::STATUS_GENERAL_ERROR
                );
            }
        }
    }

    /**
     * @param \Epicor\Comm\Model\Product $simpleProduct
     * @param \Epicor\Comm\Model\Product $configProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function _processConfigurableOption($simpleProductSku, $configProductSku, $erpData)
    {
        $parentErpData = $erpData->getParent();

        //Configurable attribute list
        $erpConfigurableAttrLists = $this->_getGroupedData('configurableAttributes', 'attribute', $parentErpData);
        if (!$erpConfigurableAttrLists) {
            $attrLists = $parentErpData->getConfigurableAttributeList() ? str_replace(', ', ',',
                $parentErpData->getConfigurableAttributeList()) : '';
            $erpConfigurableAttrLists = explode(',', $attrLists);
        }

        if (!$erpConfigurableAttrLists) {
            throw new \Exception(
                'configurableAttributes or configurableAttributeList must be require to create configurable product.',
                self::STATUS_GENERAL_ERROR
            );
        }

        //process configurable attribute


        /* @var $eavConfig \Magento\Eav\Model\Config */
        foreach ($erpConfigurableAttrLists as $erpConfigurableAttrList) {

        }
        $configurableAttributesData = [];
        $eavConfig = $this->eavConfig;
        /* @var $eavConfig \Magento\Eav\Model\Config */
        $attrOptions[] = 'sku=' . $simpleProductSku;
        $erpConfigurableAttrLists = array_map('trim', $erpConfigurableAttrLists);
        foreach ($erpConfigurableAttrLists as $erpConfigurableAttrList) {
            $attribute = $eavConfig->getAttribute('catalog_product', $erpConfigurableAttrList);
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if (!$attribute) {
                throw new \Exception(
                    "Product attribute {$erpConfigurableAttrList} not found to creat configurable product.",
                    self::STATUS_GENERAL_ERROR
                );
            }
            if (isset($this->_productDataArray[$simpleProductSku][$erpConfigurableAttrList]) &&
                $this->_productDataArray[$simpleProductSku][$erpConfigurableAttrList]
            ) {
                $attrOptions[] = $erpConfigurableAttrList . '=' . $this->_productDataArray[$simpleProductSku][$erpConfigurableAttrList];
            } else {
                throw new \Magento\Framework\Exception\InputException(
                    __('Product with SKU "%1" does not contain required attribute "%2".', $simpleProductSku, $erpConfigurableAttrList)
                );
            }
        }
        $configurableVariations = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $attrOptions);
        $this->_productDataArray[$configProductSku]['configurable_variations'] = $configurableVariations;


        //Validate link product
        if ($this->_exists()) {// no need to check when simple product creating
            //get all parent's

            $simpleProductId = isset($this->_productExitingDataArray[$simpleProductSku]['entity_id']) ?
                $this->_productExitingDataArray[$simpleProductSku]['entity_id'] : false;
            $configProductId = isset($this->_productExitingDataArray[$configProductSku]['entity_id']) ?
                $this->_productExitingDataArray[$configProductSku]['entity_id'] : false;

            if (!$simpleProductId) {
                return $this;
            }

            $parentIds = $this->configurableType->getParentIdsByChild($simpleProductId);
            if ($parentIds && !in_array($configProductId, $parentIds)) {
                foreach ($parentIds as $oldParentId) {
                    $oldConfigProduct = null;
                    $childs = $this->configurableType->getChildrenIds($oldParentId);
                    if (isset($childs[0])) {
                        $childs = $childs[0];
                    }

                    //Only one associated to configurable then delete product itself or unassigned
                    if (count($childs) > 1) {
                        $connection = $this->resourceConnection->getConnection();
                        $connection->delete(
                            'catalog_product_super_link',
                            ['parent_id = ?' => $oldParentId, 'product_id = ?' => $simpleProductId]
                        );
                        //$connection->query("delete from catalog_product_super_link where parent_id=".$oldParentId." AND product_id='".$simpleProductId."'");
                    } else {
                        try {
                            $oldConfigProduct = $this->_productRepositoryInterface->getById($oldParentId);
                            /* @var $oldConfigProduct \Magento\Catalog\Model\Product */
                            $this->_deleteProduct($oldConfigProduct);
                        } catch (\Exception $e) {
                            throw new \Exception('something went wrong when delete configurable product:' . ' (' . $e->getMessage() . ')');
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Comm\Model\Product $configProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param bool $isnew
     */
    protected function _processConfigurableData($simpleProductSku, $configProductSku, $erpData, $isnew = false)
    {
        $id = $configProductSku;
        $parentErpData = $erpData->getParent();
        $type = $this->_productType;

        //no Update require for Configurable product
        if ($isnew) {
            //Name
            $this->_checkAndSetAttribute($id, 'name', $parentErpData->getTitle() ?: $erpData->getTitle());

            //Short Description
            $this->_checkAndSetAttribute($id, 'short_description',
                $parentErpData->getShortDescription() ?: $erpData->getShortDescription());

            //Description
            $this->_checkAndSetAttribute($id, 'description',
                $parentErpData->getDescription() ?: $erpData->getDescription());

            //Weight
            $this->_checkAndSetAttribute($id, 'weight', $this->_getWeight($erpData));

            $this->_setAttribute($id, 'ecc_stk_type', $type);

            $googleFeed = $this->_flags->getGoogle();
            $showInGoogle = (strtoupper($googleFeed) === 'Y') ? 1 : 0;
            $this->_checkAndSetAttribute($id, 'ecc_google_feed', $showInGoogle);

            //Related Documents
            $documents = $this->_getGroupedData('related_documents', 'related_document', $parentErpData);
            if (count($documents) > 0) {
                $relatedDocvalue = $this->_getRelatedDocuments($parentErpData, $configProductSku);
                if (!empty($relatedDocvalue)) {
                    $this->_checkAndSetAttribute($id, 'ecc_related_documents', $relatedDocvalue);
                }
            } else {
                $relatedDocvalue = $this->_getRelatedDocuments($erpData, $configProductSku);
                if (!empty($relatedDocvalue)) {
                    $this->_checkAndSetAttribute($id, 'ecc_related_documents', $relatedDocvalue);
                }
            }

            //Images
            $newImages = $this->_getGroupedData('images', 'image', $parentErpData);
            if (count($newImages) > 0) {
                $this->_processErpImages($parentErpData, $configProductSku);
            } else {
                $this->_processErpImages($erpData, $configProductSku);
            }

            //process for single UOM
            $this->_processUom($erpData, $configProductSku);

            //Meta Information
            $this->_processMetaInformation($erpData, $configProductSku);
            $this->_checkAndSetEccBrand($id, 'ecc_brand', $erpData->getSupplierBrand());
            $this->_checkAndSetEccBrandD($id, $erpData->getSupplierBrand());
        }

        $currencies = $this->_getCurrencies($erpData);
        $this->_processVisibility($erpData, $configProductSku, $currencies);

        /**
         * We can't delete location from parent(configurable) product
         */
        $this->_processLocations($erpData, $configProductSku);

        if ($this->_cacheState->isEnabled('message')) {
            $this->_cacheManager->clean($configProductSku);
        }
    }

    /**
     * Process Currency data into a useable array
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     *
     * return array
     */
    protected function _getCurrencies($erpData)
    {
        $currencies = array();
        $erpCurrencies = $this->_getGroupedData('currencies', 'currency', $erpData);

        foreach ($erpCurrencies as $currency) {
            if ($currency) {
                $code = $this->getHelper()->getCurrencyMapping($currency->getCurrencyCode(),
                    \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
                $price = $currency->getBasePrice();
                $cost = $currency->getCostPrice();
                $currencies[$code]['base_price'] = $price ?: 0;
                $currencies[$code]['cost_price'] = $cost ?: 0;
            }
        }

        if (!array_key_exists($this->storeManager->getStore()->getBaseCurrencyCode(), $currencies)) {
            $currencies[$this->storeManager->getStore()->getBaseCurrencyCode()]['base_price'] = 0;

            $this->_baseCurrencyIncluded = false;
        }

        return $currencies;
    }

    /**
     * Gets manufacturer data from the message
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     *
     * @return array
     */
    protected function _getManufacturers($erpData)
    {
        $mpn = array();
        $manufacturers = $this->_getGroupedData('manufacturers', 'manufacturer', $erpData);
        foreach ($manufacturers as $manufacturer) {
            $mpn[] = array(
                'name' => $manufacturer->getName(),
                'product_code' => $manufacturer->getProductCode()
            );
        }

        return $mpn;
    }

    /**
     * Gets the lead time data from the message
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     *
     * @return string
     */
    protected function _getLeadTime($erpData, $loadSku)
    {
        $leadTime = isset($this->_productExitingDataArray[$loadSku]['ecc_lead_time']) ?
            $this->_productExitingDataArray[$loadSku]['ecc_lead_time'] : null;
        $leadTimeArray = array();
        $leadTimeGroup = $erpData->getLeadTime();
        if ($leadTimeGroup) {
            $leadTimeDays = $leadTimeGroup->getDays();
            $leadTimeText = $leadTimeGroup->getText();

            if ($leadTime) {
                $leadTimeArray = explode(" ", $leadTime, 2);
                if (!$this->isUpdateable('lead_time_days_update',
                    $this->_exists())) {  // if not new and not updatable use exsiting value
                    $leadTimeDays = isset($leadTimeArray[0]) ? $leadTimeArray[0] : null;
                }
                if (!$this->isUpdateable('lead_time_text_update', $this->_exists())) {
                    $leadTimeText = isset($leadTimeArray[1]) ? $leadTimeArray[1] : null;
                }
            }

            if ($leadTimeDays || $leadTimeText) {
                $leadTime = '';
                if ($leadTimeDays) {
                    $leadTime .= $leadTimeDays;
                    if (!$leadTimeText) {
                        ($leadTimeDays > 1) ? $leadTime .= " days" : $leadTime .= " day";
                    }
                }
                if ($leadTimeText) {
                    $leadTime .= " " . $leadTimeText;
                }
            }
        }

        return $leadTime;
    }

    /**
     * Gets the product Weight from the message
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     *
     * @return string
     */
    protected function _getWeight($erpData)
    {
        $weight = (isset($this->_defaultUom['weight']) && $this->_productType === 'simple') ? $this->_defaultUom['weight'] : $erpData->getWeight();
        if ($weight == 0 && $this->getConfigFlag('default_on_zero_weight')) {
            $weight = $this->getConfig('default_weight_value');
        }
        return $weight;
    }

    /**
     * Gets related document data from the message
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     *
     * @return array
     */

    protected function _getRelatedDocuments($erpData, $loadSku)
    {
        $docSyncRequired = false;
        $documents = $this->_getGroupedData('related_documents', 'related_document', $erpData);
        $productRelatedDocuments = isset($this->_productExitingDataArray[$loadSku]['ecc_related_documents']) ?
            $this->_productExitingDataArray[$loadSku]['ecc_related_documents'] : [];

        if (!is_array($productRelatedDocuments)) {
            $productRelatedDocuments = unserialize($productRelatedDocuments);
        }
        if (!is_array($productRelatedDocuments)) {
            $productRelatedDocuments = array();
        }

        $relatedDocuments = array();
        foreach ($productRelatedDocuments as $key => $document) {
            if (!isset($document['is_erp_document']) || !$document['is_erp_document']) {
                $relatedDocuments[] = $document;
            }
        }

        foreach ($documents as $document) {
            $description = $document->getDescription();
            $url = $attachmentNumber = $erpFileId = $webFileId = $attachmentStatus = '';
            $filename = $document->getFilename();
            $syncRequired = 'N';
            if ($attachment = $document->getAttachment()) {
                $description = $attachment->getDescription();
                $url = $attachment->getUrl();
                $filename = $attachment->getFilename();
                $attachmentNumber = $attachment->getAttachmentNumber();
                $erpFileId = $attachment->getErpFileId();
                $webFileId = $attachment->getWebFileId();
                $attachmentStatus = $attachment->getAttachmentStatus();
            }

            if ($url) {
                $filename = $url;
            }

            if ($erpFileId || $attachmentStatus == "R" || $url) {
                $docSyncRequired = true;
                $syncRequired = 'Y';
            }

            $relatedDocuments[] = array(
                'description' => $description,
                'filename' => $filename,
                'url' => $url,
                'is_erp_document' => '1',
                'attachment_number' => $attachmentNumber,
                'erp_file_id' => $erpFileId,
                'web_file_id' => $webFileId,
                'attachment_status' => $attachmentStatus,
                'sync_required' => $syncRequired
            );
        }

        if ($docSyncRequired) {
            $this->_checkAndSetAttribute($loadSku, 'ecc_related_documents_synced', 0);
        }
        return $relatedDocuments;
    }


    /**
     * Add EWA Code product custom option
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _processConfiguratorOption($loadSku)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $optionData = ['name' => 'Ewa Description', 'type' => 'ewa_description',
            'sku' => $loadSku, 'product_sku' => $loadSku, 'required' => 0, 'sort_order'];
        $productExisitingOptions['ewa_description'] = $this->optionRowToCellString($optionData);

        $optionData = '';
        $optionData = ['name' => 'Ewa Code', 'type' => 'ewa_code',
            'sku' => $loadSku, 'product_sku' => $loadSku, 'required' => 0, 'sort_order'];
        $productExisitingOptions['ewa_code'] = $this->optionRowToCellString($optionData);

        $optionData = '';
        $optionData = ['name' => 'Ewa SKU', 'type' => 'ewa_sku',
            'sku' => $loadSku, 'product_sku' => $loadSku, 'required' => 0, 'sort_order'];
        $productExisitingOptions['ewa_sku'] = $this->optionRowToCellString($optionData);

        $optionData = '';
        $optionData = ['name' => 'Ewa Short Description', 'type' => 'ewa_short_description',
            'sku' => $loadSku, 'product_sku' => $loadSku, 'required' => 0, 'sort_order'];
        $productExisitingOptions['ewa_short_description'] = $this->optionRowToCellString($optionData);

        $optionData = '';
        $optionData = ['name' => 'Ewa Title', 'type' => 'ewa_title',
            'sku' => $loadSku, 'product_sku' => $loadSku, 'required' => 0, 'sort_order'];
        $productExisitingOptions['ewa_title'] = $this->optionRowToCellString($optionData);

        $customOptions = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $productExisitingOptions);
        $this->_setStaticAttribute($loadSku, 'can_save_custom_options', 1);
        $this->_productDataArray[$loadSku]['custom_options'] = $customOptions;
        $this->_productDataArray[$loadSku]['has_options'] = 1;
    }

    /**
     * Removes EWA options from product
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _removeConfiguratorOption($loadSku)
    {
        $productId = isset($this->_productExitingDataArray[$loadSku]['entity_id']) ?
            $this->_productExitingDataArray[$loadSku]['entity_id'] : 0;
        if ($productId) {
            $codes = [
                'ewa_code',
                'ewa_description',
                'ewa_short_description',
                'ewa_title',
                'ewa_sku',
            ];
            $connection = $this->resourceConnection->getConnection();
            $optionTableName = $connection->getTableName('catalog_product_option');
            $connection->delete($optionTableName, ['product_id = ?' => $productId, 'type IN (?)' => $codes]);

            $sql = $connection->select()
                ->from($optionTableName, 'count(*)')
                ->where('product_id = ?', $productId);
            $count = $connection->fetchOne($sql);
            if ($count) {
                $this->_setStaticAttribute($loadSku, 'has_options', 1);
            } else {
                $this->_setStaticAttribute($loadSku, 'has_options', 0);
            }
        }
    }

    /**
     * Retrieve Bundle exiting.
     *
     * @return array
     */
    protected function retrieveBundleProductsSkus($productId)
    {
        $connection = $this->resourceConnection->getConnection();
        $cachedSkuToProducts = $connection->fetchCol(

            $connection->select()->from(
                ['sec' => $connection->getTableName('catalog_product_bundle_selection')],
                []
            )->joinLeft(
                array(
                    'cp' => $connection->getTableName('catalog_product_entity')
                ), 'sec.product_id = cp.entity_id', ['cp.sku']
            )->where(
                'sec.parent_product_id =?',
                $productId
            )
        );
        return $cachedSkuToProducts;
    }

    /**
     * Check products exists.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function checkPoductExist($sku, $uom)
    {
        $connection = $this->resourceConnection->getConnection();
        $uomSeperator = $this->getHelper()->getUOMSeparator();
        $newsku = $sku . $uomSeperator . $uom;
        $cachedSkuToProducts = $connection->fetchRow(
            $connection->select()->from(
                $connection->getTableName('catalog_product_entity'),
                ['sku', 'entity_id']
            )->where(
                'sku =?',
                $newsku
            )
        );
        if (!isset($cachedSkuToProducts['entity_id'])) {
            $cachedSkuToProducts = $connection->fetchRow(
                $connection->select()->from(
                    ['eav' => $connection->getTableName('eav_attribute')],
                    []
                )->joinLeft(
                    array(
                        'varchar' => $connection->getTableName('catalog_product_entity_varchar')
                    ), 'varchar.attribute_id = eav.attribute_id', []
                )->joinLeft(
                    array(
                        'cp' => $connection->getTableName('catalog_product_entity')
                    ), 'varchar.entity_id = cp.entity_id', ['cp.sku', 'cp.entity_id']
                )->where(
                    'cp.sku =?',
                    $sku
                )->where(
                    'varchar.value=?',
                    $uom
                )
            );
        }
        return $cachedSkuToProducts;
    }

    /**
     * Processes bundle configurable options
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     *
     * @throws \Exception
     */
    protected function _processBundleOptions($erpData, $loadSku)
    {
        $options = array();
        $optionSelections = array();
        $optionSkus = array();
        $optionCount = 0;

        $parts = $this->_getGroupedData('exploded_parts', 'exploded_part', $erpData);

        foreach ($parts as $explodedPart) {
            $productCode = $explodedPart->getProductCode();
            $productUom = $explodedPart->getUnitOfMeasureCode();
            $qty = $explodedPart->getQuantity();
            $description = $explodedPart->getDescription();
            if (!$description) {
                $description = $productCode . " " . $productUom;  // if no description in sku, load sku and uom only
            }
            $errorCode = false;

            $productsExist = $this->checkPoductExist($productCode, $productUom);
            if (!isset($productsExist['entity_id'])) {
                $errorCode = self::STATUS_EXPLODED_PRODUCT_NOT_FOUND;
                throw new \Exception(
                    $this->getErrorDescription($errorCode, $productCode, $productUom), self::STATUS_INVALID_PRODUCT_CODE
                );
            }
            $productCode = $productsExist['sku'];
            $optionSkus[] = $productCode;
            $optionSelections = array(
                'name' => 'name=' . $description,
                'sku' => 'sku=' . $productCode,
                'required' => 'required=1',
                'type' => 'type=select',
                'price' => 'price=0.00',
                'default_qty' => 'default_qty=' . $qty,
                'default' => 'default=1',
                'price_type' => 'price_type=' . $this->getBundlePriceTypeValue(0),
                'can_change_qty' => 'can_change_qty=0',
            );
            $optionCount++;
            $options[] = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $optionSelections);
        }

        $productId = isset($this->_productExitingDataArray[$loadSku]['entity_id']) ?
            $this->_productExitingDataArray[$loadSku]['entity_id'] : 0;
        $weightType = $this->getConfigFlag('kit_weight_fixed') ? 1 : 0;
        $weightType = $this->getBundleTypeValue($weightType);
        $this->_productDataArray[$loadSku][RowCustomizer::BUNDLE_WEIGHT_TYPE_COL] = $weightType;

        $skuType = $this->getBundleTypeValue(1);
        $this->_productDataArray[$loadSku][RowCustomizer::BUNDLE_SKU_TYPE_COL] = $skuType;

        $priceType = $this->getBundleTypeValue(1);
        $this->_productDataArray[$loadSku][RowCustomizer::BUNDLE_PRICE_TYPE_COL] = $priceType;

        $priceView = $this->getBundlePriceViewValue(0);
        $this->_productDataArray[$loadSku][RowCustomizer::BUNDLE_PRICE_VIEW_COL] = $priceView;

        $this->_productDataArray[$loadSku]['bundle_shipment_type'] = 'together';

        $this->_productDataArray[$loadSku]['weight_type'] = $weightType;
        $this->_productDataArray[$loadSku]['sku_type'] = $skuType;
        $this->_productDataArray[$loadSku]['price_type'] = $priceType;
        $this->_productDataArray[$loadSku]['price_view'] = $priceView;
        $this->_productDataArray[$loadSku]['shipment_type'] = 'together';

        if ($productId) {
            $bundleproducts = $this->retrieveBundleProductsSkus($productId);
            if ($bundleproducts != $optionSkus) {
                $connection = $this->resourceConnection->getConnection();
                $connection->delete(
                    $connection->getTableName('catalog_product_bundle_option'),
                    ['parent_id = ?' => $productId]
                );
            }
        }
        $optionvalue = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $options);
        $this->_productDataArray[$loadSku]['bundle_values'] = $optionvalue;
    }

    /**
     * Retrieve bundle type value by code
     *
     * @param string $type
     * @return string
     */
    protected function getBundleTypeValue($type)
    {
        return $this->bundleTypeMapping[$type] ?? RowCustomizer::VALUE_DYNAMIC;
    }

    /**
     * Retrieve bundle price view value by code
     *
     * @param string $type
     * @return string
     */
    protected function getBundlePriceViewValue($type)
    {
        return $this->bundlePriceViewMapping[$type] ?? RowCustomizer::VALUE_PRICE_RANGE;
    }

    /**
     * Retrieve bundle price type value by code
     *
     * @param string $type
     * @return string
     */
    protected function getBundlePriceTypeValue($type)
    {
        return $this->bundlePriceTypeMapping[$type] ?? null;
    }

    /**
     * Processes uom related data
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function _processUom($erpData, $loadSku)
    {
        $uom = $erpData->getUomSalesDescription() ?: $erpData->getData('uomsales_description') ?: '';

        $packSize = $erpData->getPackSize() ?: $uom;

        $decimalPlaces = $erpData->getDecimalPlaces();

        if (empty($packSize)) {
            $packSize = $uom;
        }

        if (!$this->_processingChildren) {
            $defaultCode = isset($this->_defaultUom['code']) ? $this->_defaultUom['code'] : '';
            $defaultDesc = isset($this->_defaultUom['desc']) ? $this->_defaultUom['desc'] : '';
            $defaultDecimalPlaces = isset($this->_defaultUom['decimal_places']) ? $this->_defaultUom['decimal_places'] : '';

            if (!empty($defaultCode)) {
                $uom = $defaultCode;
                $this->_checkAndSetAttribute($loadSku, 'ecc_default_uom', $defaultCode);
            }

            if (!empty($defaultDesc)) {
                $packSize = $defaultDesc;
            }

            if (!empty($defaultDecimalPlaces)) {
                $decimalPlaces = $defaultDecimalPlaces;
            }
        } else {
            $this->_checkAndSetAttribute($loadSku, 'ecc_default_uom', null);
        }

        $this->_checkAndSetAttribute($loadSku, 'ecc_uom', $uom);
        $this->_checkAndSetAttribute($loadSku, 'ecc_decimal_places', $decimalPlaces);
        $this->_checkAndSetAttribute($loadSku, 'ecc_pack_size', $packSize);
        $this->setPackSizeConfig($loadSku, $uom, $packSize);
    }

    public function setPackSizeConfig($loadSku, $uom = null, $packSize = null)
    {
        $stkPackSize = $this->scopeConfig->getValue('epicor_comm_field_mapping/stk_mapping/stk_display_pack_size_as',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        switch ($stkPackSize) {
            case "1":
                $this->_checkAndSetAttribute($loadSku, 'ecc_pack_size', $packSize);
                break;
            case "2":
                $this->_checkAndSetAttribute($loadSku, 'ecc_pack_size', $uom);
                break;
            case "3":
                $stkConcatenationText = $this->scopeConfig->getValue('epicor_comm_field_mapping/stk_mapping/stk_concatenation_text',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $this->_checkAndSetAttribute($loadSku, 'ecc_pack_size',
                    $uom . " " . $stkConcatenationText . " " . $packSize);
                break;
            default:
                $this->_checkAndSetAttribute($loadSku, 'ecc_pack_size', $packSize);

        }
    }

    /**
     * Processes ERP image data
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function _processErpImages($erpData, $loadSku)
    {
        if (!$this->isUpdateable('images_update', $this->_exists())) {
            return;
        }

        if (!$this->_processingUOMs) {
            $newImages = $this->_getGroupedData('images', 'image', $erpData);
            $this->_loadStores($erpData);

            $erpImages = isset($this->_productExitingDataArray[$loadSku]['ecc_erp_images']) ? $this->_productExitingDataArray[$loadSku]['ecc_erp_images'] : [];
            if (!is_array($erpImages)) {
                $erpImages = unserialize($erpImages);
            }
            $erpImagestmp = $erpImages;
            $imageKeys = array();

            if (!is_array($erpImages)) {
                $erpImages = array();
            }

            // get exisiting filesnames so we can update if necessary
            if (!empty($erpImages)) {
                foreach ($erpImages as $x => $image) {
                    $imageKeys[$image['filename']] = $x;
                }
            }

            $storeImages = array();

            // loop through new images finding them in / adding them to the current image array
            foreach ($newImages as $newImage) {
                $filename = $newImage->getFilename();
                $description = $newImage->getDescription();
                $atts = $newImage->getData('_attributes') ?: $this->dataObjectFactory->create();
                $position = $atts->getNumber();

                $attachmentData = $newImage->getAttachment() ? $newImage->getAttachment()->getData() : array();

                if (!empty($attachmentData)) {
                    $filename = !empty($attachmentData['filename']) ? $attachmentData['filename'] : $filename;
                    $description = !empty($attachmentData['description']) ? $attachmentData['description'] : $description;
                }
                $storeImages[] = $filename;

                preg_match_all("/[a-zA-Z]/", $atts->getType(), $types);
                $types = isset($types[0]) ? $types[0] : null;

                if (isset($imageKeys[$filename])) {
                    if ($this->isUpdateable('images_update', $this->_exists())) {
                        $existingImage = $erpImages[$imageKeys[$filename]];
                        $existingImage['description'] = $description;
                        $existingImage['types'] = $types;
                        $existingImage['position'] = $position;

                        $existingImage = array_merge($existingImage, $attachmentData);
                        if (!empty($this->_stores)) {
                            foreach ($this->_stores as $store) {

                                if (!in_array($store->getId(), $existingImage['stores'])) {
                                    if (!isset($existingImage['stores'])) {
                                        $existingImage['stores'] = array();
                                    }
                                    if (!isset($existingImage['store_info'])) {
                                        $existingImage['store_info'] = array();
                                    }
                                    $existingImage['stores'][] = $store->getId();
                                    $existingImage['store_info'][$store->getId()] = array(
                                        'description' => $description,
                                        'types' => $types,
                                        'position' => $position,
                                        'STK' => 1,
                                        'STT' => 0
                                    );
                                } else {

                                    $existingImage['store_info'][$store->getId()]['STK'] = 1;
                                    if ($existingImage['store_info'][$store->getId()]['STK'] &&
                                        !$existingImage['store_info'][$store->getId()]['STT']) {
                                        if ($this->isUpdateable('product_images_image_description_update',
                                            $this->_exists())) {
                                            $existingImage['store_info'][$store->getId()]['description'] = $description;
                                        }
                                        $existingImage['store_info'][$store->getId()]['types'] = $types;
                                        $existingImage['store_info'][$store->getId()]['position'] = $position;
                                    }
                                }
                            }
                        }

                        $erpImages[$imageKeys[$filename]] = $existingImage;
                    }
                } else {
                    $stores = array();
                    $storeInfo = array();
                    if (!empty($this->_stores)) {
                        foreach ($this->_stores as $store) {
                            $stores[] = $store->getId();
                            $storeInfo[$store->getId()] = array(
                                'description' => $description,
                                'types' => $types,
                                'position' => $position,
                                'STK' => 1,
                                'STT' => 0
                            );
                        }
                    }

                    $erpImages[] = array_merge(array(
                        'description' => $description,
                        'filename' => $filename,
                        'types' => $types,
                        'position' => $position,
                        'media_filename' => null,
                        'stores' => $stores,
                        'store_info' => $storeInfo,
                        'status' => 0
                    ), $attachmentData);
                }
            }

            // loop through and remove stores from any images not provided in this STK
            // as long as they've not been sent in an STT
            if (!empty($erpImages)) {
                foreach ($erpImages as $x => $image) {
                    if (!in_array($image['filename'], $storeImages)) {
                        foreach ($this->_stores as $store) {
                            $key = array_search($store->getId(), $image['stores']);
                            if ($key !== false) {
                                if (!$erpImages[$x]['store_info'][$store->getId()]['STT']) {
                                    unset($erpImages[$x]['stores'][$key]);
                                    unset($erpImages[$x]['store_info'][$store->getId()]);
                                } else {
                                    $erpImages[$x]['store_info'][$store->getId()]['STK'] = 0;
                                }
                            }
                        }
                    }
                }
            }

            $this->_setAttribute($loadSku, 'ecc_previous_erp_images', $erpImagestmp);
            $this->_setAttribute($loadSku, 'ecc_erp_images', $erpImages);
            if (!empty($erpImages)) {
                $this->_setAttribute($loadSku, 'ecc_erp_images_processed', 0);
            } else {
                $this->_setAttribute($loadSku, 'ecc_erp_images_processed', 1);
            }
        } else {
            $this->_setAttribute($loadSku, 'ecc_erp_images_processed', 1);
        }
    }

    protected function getProductCustomOptions($productId, $storeId)
    {
        $productIds = is_array($productId) ? $productId : [$productId];
        $customOptionsData = [];
        $exisitingEccCode = [];
        $defaultOptionsData = [];
        $options = $this->_optionColFactory->create();
        /* @var Collection $options */
        $options->reset()
            ->addOrder('sort_order', Collection::SORT_ORDER_ASC)
            ->addTitleToResult($storeId)
            ->addPriceToResult($storeId)
            ->addProductToFilter($productIds)
            ->addValuesToResult($storeId);

        foreach ($options as $option) {
            $optionData = $option->toArray();
            $row = [];
            $productId = $option['product_id'];
            $row['name'] = $option['title'];
            $row['type'] = $option['type'];

            $row['required'] = $this->getOptionValue('is_require', $defaultOptionsData, $optionData);
            $row['price'] = $this->getOptionValue('price', $defaultOptionsData, $optionData);
            $row['sku'] = $this->getOptionValue('sku', $defaultOptionsData, $optionData);
            $row['ecc_code'] = $this->getOptionValue('ecc_code', $defaultOptionsData, $optionData);
            $row['ecc_default_value'] = $this->getOptionValue('ecc_default_value', $defaultOptionsData, $optionData);
            $row['ecc_validation_code'] = $this->getOptionValue('ecc_validation_code', $defaultOptionsData, $optionData);
            if (array_key_exists('max_characters', $optionData)
                || array_key_exists('max_characters', $defaultOptionsData)
            ) {
                $row['max_characters'] = $this->getOptionValue('max_characters', $defaultOptionsData, $optionData);
            }
            foreach (['file_extension', 'image_size_x', 'image_size_y'] as $fileOptionKey) {
                if (isset($option[$fileOptionKey]) || isset($defaultOptionsData[$fileOptionKey])) {
                    $row[$fileOptionKey] = $this->getOptionValue($fileOptionKey, $defaultOptionsData, $optionData);
                }
            }
            $percentType = $this->getOptionValue('price_type', $defaultOptionsData, $optionData);
            $row['price_type'] = ($percentType === 'percent') ? 'percent' : 'fixed';

            if (Store::DEFAULT_STORE_ID === $storeId) {
                $optionId = $option['option_id'];
                $defaultOptionsData[$optionId] = $option->toArray();
            }

            $values = $option->getValues();

            if ($values) {
                foreach ($values as $value) {
                    $row['option_title'] = $value['title'];
                    $row['option_title'] = $value['title'];
                    $row['price'] = $value['price'];
                    $row['price_type'] = ($value['price_type'] === 'percent') ? 'percent' : 'fixed';
                    $row['sku'] = $value['sku'];
                    if ($row['ecc_code']) {
                        $customOptionsData[$productId][$storeId][$row['ecc_code']] = $this->optionRowToCellString($row);
                    } else {
                        $customOptionsData[$productId][$storeId][] = $this->optionRowToCellString($row);
                    }
                }
            } else {
                if ($row['ecc_code']) {
                    $customOptionsData[$productId][$storeId][$row['ecc_code']] = $this->optionRowToCellString($row);
                } else {
                    $customOptionsData[$productId][$storeId][] = $this->optionRowToCellString($row);
                }
            }
            $option = null;
            if ($row['ecc_code']) {
                $exisitingEccCode[$row['ecc_code']] = $row['ecc_code'];
            }
        }
        $options = null;
        if (!empty($customOptionsData)) {
            return ['customOptionsData' => $customOptionsData[$productId][$storeId], 'exisitingEccCode' => $exisitingEccCode];
        }
        return $customOptionsData;
    }


    /**
     * Get value for custom option according to store or default value
     *
     * @param string $optionName
     * @param array $defaultOptionsData
     * @param array $optionData
     * @return mixed
     */
    private function getOptionValue($optionName, $defaultOptionsData, $optionData)
    {
        $optionId = $optionData['option_id'];

        if (array_key_exists($optionName, $optionData) && $optionData[$optionName] !== null) {
            return $optionData[$optionName];
        }

        if (array_key_exists($optionId, $defaultOptionsData)
            && array_key_exists($optionName, $defaultOptionsData[$optionId])
        ) {
            return $defaultOptionsData[$optionId][$optionName];
        }

        return null;
    }

    /**
     * Convert option row to cell string
     *
     * @param array $option
     * @return string
     */
    protected function optionRowToCellString($option)
    {
        $result = [];

        foreach ($option as $key => $value) {
            $result[] = $key . ImportProduct::PAIR_NAME_VALUE_SEPARATOR . $value;
        }

        return implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $result);
    }

    /**
     * Processes optoins data
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function _processCustomOptions($erpData, $loadSku)
    {
        if (!$this->isUpdateable('custom_options_update', $this->_exists())) {
            return;
        }
        $productExisitingOptions = [];
        $exisitingEccCode = [];
        $productId = isset($this->_productExitingDataArray[$loadSku]['entity_id']) ?
            $this->_productExitingDataArray[$loadSku]['entity_id'] : 0;
        if ($productId) {
            $productExisitingOptionsreturn = $this->getProductCustomOptions($productId, Store::DEFAULT_STORE_ID);
            $productExisitingOptions = isset($productExisitingOptionsreturn['customOptionsData']) ?
                $productExisitingOptionsreturn['customOptionsData'] : [];
            $exisitingEccCode = isset($productExisitingOptionsreturn['exisitingEccCode']) ?
                $productExisitingOptionsreturn['exisitingEccCode'] : [];
        }

        $newOptions = $this->_getGroupedData('options', 'option', $erpData);

        $ewaTypes = array(
            'ewa_code',
            'ewa_description',
            'ewa_short_description',
            'ewa_title',
            'ewa_sku',
        );


        $hasErpOptions = false;
        $remomveoptions = $exisitingEccCode;

// locate the configurator option on the product, so we don;t add it twice
        $uploadedCodes = array();
        if (!empty($newOptions)) {

            $hasErpOptions = true;

            foreach ($newOptions as $newOption) {
                $code = $newOption->getCode();
                if (in_array($code, $ewaTypes)) {
                    continue;
                }
                $uploadedCodes[] = $code;
                $sortOrder = (int)$newOption->getData('_attributes')->getNumber();

                if (!isset($this->_optionTypes[$newOption->getType()])) {
                    throw new \Exception(
                        $this->getErrorDescription(
                            self::STATUS_GENERAL_ERROR, 'Invalid option type ' . $newOption->getType()
                        ), self::STATUS_GENERAL_ERROR
                    );
                }

                $optionType = $this->_optionTypes[$newOption->getType()];

                $isRequired = (strtoupper($newOption->getIsRequired()) == 'Y') ? 1 : 0;
                $max_characters = ($newOption->getLimit()) ? $newOption->getLimit() : 0;
                $optionData = array();


                //$optionData['title'] = $newOption->getDescription();
                $optionData['name'] = $newOption->getDescription();
                $optionData['type'] = $optionType;
                $optionData['required'] = $isRequired;
                $optionData['sort_order'] = $sortOrder;
                if (!in_array($optionType, array('ecc_text_field', 'ecc_text_hidden'))) {
                    $optionData['values'] = $this->_getOptionValues($newOption);
                }

                $optionData['sku'] = '';
                $optionData['price'] = 0;
                $optionData['max_characters'] = $max_characters;
                $optionData['file_extension'] = '';
                $optionData['image_size_x'] = 0;
                $optionData['image_size_y'] = 0;
                $optionData['price_type'] = 'fixed';
                $optionData['required'] = $isRequired;
                $optionData['ecc_code'] = $newOption->getCode();
                $optionData['ecc_default_value'] = $newOption->getDefaultValue();
                $optionData['ecc_validation_code'] = $newOption->getValidationCode();
                $productExisitingOptions[$code] = $this->optionRowToCellString($optionData);
                unset($remomveoptions[$code]);
            }
        }

        $removeOptios = [];
        foreach ($remomveoptions as $newEccCode) {
            if (isset($productExisitingOptions[$newEccCode])) {
                $removeOptios[] = $productExisitingOptions[$newEccCode];
                unset($productExisitingOptions[$newEccCode]);
            }
        }
        if (count($removeOptios) > 0) {
            $removeEcccustomOptions = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $removeOptios);
            $this->_productDataArray[$loadSku]['remove_custom_options'] = $removeEcccustomOptions;
        }
        if (count($productExisitingOptions) > 0 || $this->_stkType == 'C') {
            $hasOptions = 1;
            $customOptions = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $productExisitingOptions);
            //  $customOptions = 'name=text,type=field,required=1,price=405.000000,sku=text1,max_characters=12,file_extension=,image_size_x=0,image_size_y=0,price_type=fixed|name=dropsown,type=drop_down,required=1,price=20.000000,sku=drop2,max_characters=0,file_extension=,image_size_x=0,image_size_y=0,price_type=percent,option_title=drop2|name=dropsown,type=drop_down,required=1,price=12.000000,sku=drop3,max_characters=0,file_extension=,image_size_x=0,image_size_y=0,price_type=fixed,option_title=drop3|name=Engraving,type=ecc_text_field,required=0,sort_order=1,sku=Engraving,price=,file_extension=,image_size_x=,image_size_y=,price_type=fixed,max_characters=,ecc_code=OP1,ecc_default_value=Enter Engraving here,ecc_validation_code=';
            $this->_setStaticAttribute($loadSku, 'can_save_custom_options', 1);
            if ($customOptions && isset($this->_productDataArray[$loadSku]['custom_options']) && $this->_productDataArray[$loadSku]['custom_options']) {
                $customOptions = $this->_productDataArray[$loadSku]['custom_options'] . '|' . $customOptions;
            }
            if ($customOptions) {
                $this->_productDataArray[$loadSku]['custom_options'] = $customOptions;
            }
        } else {
            $hasOptions = 0;
        }
        $this->_productDataArray[$loadSku]['has_options'] = $hasOptions;


        return true;

    }

    /**
     * gets an options values from the erp data
     *
     * @param \Epicor\Common\Model\Xmlvarien $option
     *
     * @return array
     */
    protected function _getOptionValues($option)
    {
        $valueData = array();

        $values = $this->_getGroupedData('values', 'value', $option);
        foreach ($values as $value) {
            if ($value->getTitle()) {
                $valueData[] = array(
                    'option_title' => $value->getTitle(),
                    'sku' => $value->getSku(),
                    'sort_order' => (int)$value->getData('_attributes')->getNumber()
                );
            }
        }

        return $valueData;
    }

    /**
     * Process stock data
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function _processStock($erpData, $loadSku)
    {
        // update product stock
        // if update allowed, use xml values, else use existing qty value

        $qty = $erpData->getFreeStock();
        $inStock = ($this->scopeConfig->getValue('epicor_comm_enabled_messages/msq_request/products_always_in_stock',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) || $qty > 0) ? 1 : 0;
        $acceptsDecimal = $this->acceptsDecimalQty();
        $stockDataArray = array(
            'is_in_stock' => $inStock,
            'use_config_manage_stock' => 0,
            'is_qty_decimal' => $acceptsDecimal
        );

        $save = false;

        //check if config field is updatable. if not, keep it's existing value
        if ($this->isUpdateable('product_manage_stock_update', $this->_exists())) {
            $stockDataArray['manage_stock'] = $this->_productDataArray[$loadSku]['ecc_stk_type'] == 'grouped' ? 1 : 0;
            $save = true;
        }

        if ($this->isUpdateable('free_stock_update', $this->_exists())) {
            $stockDataArray['qty'] = $qty;
            $save = true;
        }

        if ($this->isUpdateable('product_max_order_qty_update', $this->_exists())) {
            $max = $erpData->getMaximumOrderQty() ?: false;
            $stockDataArray['max_sale_qty'] = $max;
            $stockDataArray['use_config_max_sale_qty'] = $max === false ? 1 : 0;
            $save = true;
        }
        if ($this->isUpdateable('product_min_order_qty_update', $this->_exists())) {
            $min = $erpData->getMinimumOrderQty() ?: false;
            $stockDataArray['min_sale_qty'] = $min;
            $stockDataArray['use_config_min_sale_qty'] = $min === false ? 1 : 0;
            $save = true;
        }
        if ($save) {
            $this->_productDataArray[$loadSku] = array_merge($this->_productDataArray[$loadSku], $stockDataArray);
        }
    }

    /**
     * Process Attributes Repeating Group
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     *
     */
    protected function _processAttributes($erpData, $productId)
    {
        if (!$this->isUpdateable('product_attributes_update', $this->_exists())) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $eavConfig = $this->eavConfig;
        /* @var $eavConfig \Magento\Eav\Model\Config */
        $this->getProductEnityTypeId();
        $attributes = $this->_getGroupedData('attributes', 'attribute', $erpData);
        $indexProductAttributes = array();

        $processMissingAttributes = $this->isUpdateable('ecc_process_missing_attributes', $this->_exists());
        $allSTKAttr = [];
        if ($processMissingAttributes) {
            $allSTKAttr = $this->_getAllStkAttributes($this->_attributeSet);
        }

        $start = microtime(true);
        foreach ($attributes as $attribute) {
            // can't update a code, a new code is a new attribute
            $code = strtolower($attribute->getCode());
            if (!empty($allSTKAttr) && in_array($code, $allSTKAttr)) {
                $key = array_search($code, $allSTKAttr);
                unset($allSTKAttr[$key]);
            }
            $description = $attribute->getDescription();
            $attributeSetId = $this->_attributeSet;
            /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
            $attributeSet = $this->eavEntityAttributeSet;

            $attributeId = $this->getAttributeId($code);
            $attributeGroupId = $this->getAttrGroupId($attributeId, $attributeSetId);
            if ($attributeGroupId) {
                $attributeGroupId = $attributeGroupId;
            } else {
                $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            }

            //Determine if attribute is on epicor_comm_erp_attributes. if so use settings retrieved
            //we can't use $this->commErpMappingAttributesFactory() will have give old object
            /// $attributeTable = $this->commErpMappingAttributesFactory->create()->load($code, 'attribute_code');
            $this->_erpMappingAttributes[$code] = isset($this->_erpMappingAttributes[$code]) ?
                $this->_erpMappingAttributes[$code] : null;
            $attributeModel = $this->getProductAttributeData($code);
            if (isset($attributeModel['attribute_id'])) {

                if ($this->isUpdateable('product_attributes_description_update',
                        isset($attributeModel['attribute_id'])) && $description) {   // label
                    // if already exists, update description by frontend label
                    //perforamce imrpovement END
                    if ($description != $attributeModel['frontend_label']
                        && $attributeModel['ecc_created_by'] != 'N') {
                        //  $attributeModel->setFrontendLabel($description)->save();  //done here otherwise only saved for new attributes
                        $where = ['attribute_id=?' => $attributeModel['attribute_id']];
                        $data = ['frontend_label' => $description];
                        $table = $connection->getTableName('eav_attribute');
                        $this->updateData(
                            $table,
                            $data,
                            $where
                        );
                        //$attributeModel->setFrontendLabel($description);
                    }
                    //perforamce imrpovement END
                }
                if ($this->isUpdateable('product_attributes_value_update', $this->_exists())) {
                    //If Source type is Boolean and frontend_input is either  Boolean or Select
                    // then import system accept Yes/No values
                    //If Source type is Empty and frontend_input is Boolean Then 1 or 0 value is allowed

                    if (isset($attributeModel['frontend_input']) &&
                        $attributeModel['frontend_input'] == 'select' &&
                        $attributeModel['source_model'] == 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
                    ) {
                        $attribute->setValue($this->isTrue($attribute->getValue()) ? 'Yes' : 'No');
                    }
                    if (isset($attributeModel['frontend_input']) &&
                        $attributeModel['frontend_input'] == 'boolean' && !$attributeModel['source_model']) {
                        $attribute->setValue($this->isTrue($attribute->getValue()) ? 1 : 0);
                    } elseif (isset($attributeModel['frontend_input']) &&
                        $attributeModel['frontend_input'] == 'boolean' &&
                        $attributeModel['source_model'] == 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
                    ) {
                        $attribute->setValue($this->isTrue($attribute->getValue()) ? 'Yes' : 'No');
                    }
                    $value = $attribute->getValue();
                    $this->_saveAttributeValues($attributeModel, $code, $value);
                    $this->_setAttribute($productId, $code, $value);
                    if ($this->_oldAttributeSet != $attributeSetId) {
                        $this->attributeManagement->assign(
                            'catalog_product',
                            $attributeSetId,
                            $attributeGroupId,
                            $code,
                            $this->_getAttributeSortOrder($attributeGroupId, false, $attributeModel['attribute_id'],
                                $attributeGroupId)
                        );
                    }
                }
            } else {
                $product = $productId;
                $this->_createAttribute($product, $code, $description, $attribute);
                $eavConfig = $this->eavConfigFactory->create();
                /* @var $eavConfig \Magento\Eav\Model\Config */
                $attributeModel = $eavConfig->getAttribute('catalog_product', $code);

                // convert value to 1 or 0 if boolean
                if ($attributeModel->getFrontendInput() == 'boolean') {
                    $attribute->setValue($this->isTrue($attribute->getValue()) ? 'Yes' : 'No');
                }

                //set attribute options
                $this->_saveAttributeValues($attributeModel, $code, $attribute->getValue());
                //set attribute value
                $this->_setAttribute($productId, $code, $attribute->getValue());
                $this->attributeManagement->assign(
                    'catalog_product',
                    $attributeSetId,
                    $attributeGroupId,
                    $code,
                    $this->_getAttributeSortOrder($attributeGroupId, true, $attributeModel->getId())
                );
                $this->_processNewattribute[] = $code;
            }
            $indexProductAttributes[$attributeModel['attribute_id']] = $attributeModel['attribute_code'];
        }

        foreach ($allSTKAttr as $stkAttr) {
            $code = strtolower($stkAttr);
            //Determine if attribute is on epicor_comm_erp_attributes. if so use settings retrieved
            //we can't use $this->commErpMappingAttributesFactory() will have give old object
            //$attributeTable = $this->commErpMappingAttributesFactory->create()->load($code, 'attribute_code');
            $this->_erpMappingAttributes[$code] = isset($this->_erpMappingAttributes[$code]) ?
                $this->_erpMappingAttributes[$code] : null;
            $attributeModel = $eavConfig->getAttribute('catalog_product', $code);
            if ($attributeModel instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute &&
                !$attributeModel->isObjectNew()) {
                if ($this->isUpdateable('product_attributes_value_update', $this->_exists())
                    || $processMissingAttributes) {
                    $value = '';
                    $this->_saveAttributeValues($attributeModel, $code, $value);
                    $this->_setAttribute($productId, $code, $value);
                }

            }
        }
        $this->eavConfig->clear();
        if (!empty($indexProductAttributes)) {
            $this->registry->unregister('index_product_attributes');
            $this->registry->register('index_product_attributes', $indexProductAttributes);
        }
    }

    /**
     * Get  Attribute Data
     *
     * @param string $code
     * @return array
     *
     */
    protected function getProductAttributeData($code)
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('eav_attribute');
        $sql = $connection->select()
            ->from($table)
            ->where('entity_type_id = ?', $this->_productEnityTypeId)
            ->where('attribute_code = ?', $code);
        return $connection->fetchRow($sql);

    }

    /**
     * Get  Product Enity TypeId
     *
     */
    protected function getProductEnityTypeId()
    {
        if ($this->_productEnityTypeId == 0) {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('eav_entity_type');
            $sql = $connection->select()
                ->from($table, 'entity_type_id')
                ->where('entity_type_code = ?', 'catalog_product');
            $this->_productEnityTypeId = $connection->fetchOne($sql);
        }
        return $this->_productEnityTypeId;
    }

    /**
     * Run Update Query
     *
     */
    protected function updateData($tableName, $data, $where)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->update($tableName, $data, $where);
    }

    /**
     * Get  All attribute Bu attributeSet id
     *
     */
    protected function _getAllStkAttributes($attributeSetId)
    {
        $connection = $this->resourceConnection->getConnection();
        $eavtable = $connection->getTableName('eav_attribute');
        $eavEnitiytable = $connection->getTableName('eav_entity_attribute');
        $select = $connection->select()
            ->from(['main_table' => $eavtable], 'attribute_code')
            ->join(
                ['entity_attribute' => $eavEnitiytable],
                'entity_attribute.attribute_id = main_table.attribute_id',
                []
            )
            ->where('entity_attribute.attribute_set_id = ?', $attributeSetId)
            ->where('main_table.ecc_created_by = ?', 'STK');

        return $connection->fetchCol($select);
    }

    /**
     * Attribute sort order
     *
     * @param type $attributeGroupId
     * @return type
     */
    protected function _getAttributeSortOrder($attributeGroupId, $isNew, $attributeId = null)
    {

        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('eav_entity_attribute');
        if ($isNew) {
            $select = $connection->select()
                ->from($table, 'MAX(sort_order)')
                ->where('attribute_group_id = ?', $attributeGroupId);
        } else {
            $select = $connection->select()
                ->from($table, 'MAX(sort_order)')
                ->where('attribute_id = ?', $attributeId)
                ->where('attribute_group_id = ?', $attributeGroupId);
        }

        return $connection->fetchOne($select);
    }

    /**
     * Create a new Product Attribute
     *
     * @param string $code
     * @param string $description
     */
    protected function _createAttribute($product, $code, $description, $stkAttributeDetails)
    {
        /* @var $eavConfig \Magento\Eav\Model\Config */
        $eavConfig = $this->eavConfig;
        $attribute = $eavConfig->getAttribute('catalog_product', $code);

        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute && !$attribute->isObjectNew()) {
            return;
        }
        $visible = $this->getConfigFlag('attributes_visible');

        //set new attribute values
        $requiredAttributeFields = $this->_setNewAttributeValues($code, $description, $stkAttributeDetails);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory */
        $model = $this->catalogResourceModelEavAttributeFactory->create();
        /** @var \Magento\Eav\Model\Entity\Type $entityType */
        $entityType = $this->eavEntityType->loadByCode('catalog_product');
        //get default attribute_set_id and group id
        $entityTypeId = $entityType->getEntityTypeId();
        $attributeSetId = !is_null($this->_attributeSet) ?: $entityType->getDefaultAttributeSetId();
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $this->eavEntityAttributeSet;
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $data = $this->dataForAddAttribute($model, $code, $requiredAttributeFields);
        $model->addData($data);
        $model->setEntityTypeId($entityTypeId);
        $model->setIsUserDefined($data['is_user_defined']);
        $model->setAttributeSetId($attributeSetId);
        $model->setAttributeGroupId($attributeGroupId);
        try {
            //create attribute
            $model->save();
            $this->_attributeLabelCache->clean();
        } catch (\Exception $e) {
            throw new \Exception(
                'Something went wrong with product attribute creation', self::STATUS_GENERAL_ERROR
            );
        }

        //set created by tag
        $newAttribute = $this->eavEntityAttributeFactory->create()->load($code, 'attribute_code');
        $newAttribute->setEccCreatedBy('STK');

        $newAttribute->save();
        $this->_newAttribute[$newAttribute->getId()] = $code;
        return $newAttribute;
    }

    /**
     * Determines which stores the product is enabled / visible on
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     * @param array $currencies
     */
    protected function _processVisibility($erpData, $loadSku, $currencies)
    {
        $visibilityUpdate = $this->isUpdateable('visibility_update', $this->_exists());
        $priceUpdate = $this->isUpdateable('currencies_update', $this->_exists());

        $forSale = $this->_flags->getForSale() == 'N' ? false : true;
        $defaultStores = $this->getHelper()->getDefaultStores();
        $visibleStores = $this->_loadStores();


        $allStores = $this->storeManager->getStores();
        $storeCount = count($allStores);
        //M1 > M2 Translation Begin (Rule p2-6.6)
        //$baseCurrency = Mage::app()->getBaseCurrencyCode();
        $baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();


        $visible = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH;
        $notVisible = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE;

        $enabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $disabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;


        $websites = $productRelatedDocuments = isset($this->_productExitingDataArray[$loadSku]['website_ids']) ?
            $this->_productExitingDataArray[$loadSku]['website_ids'] : [];
        $visibileWebsiteIds = array();
        $configLowestProductPrice = 0;
        $lowestPriceData = array("price" => 0, "pricingSku" => $erpData->getProductCode()); // pricing SKU
        $productPrice = false;
        foreach ($visibleStores as $store) {
            /* @var $store Mage_Core_Model_Store */
            /* grap website id for visible store ids */
            $storeWebsiteId = $this->storeStoreFactory->create()->load($store->getId())->getWebsiteId();
            if (!in_array($storeWebsiteId, $visibileWebsiteIds)) {
                $visibileWebsiteIds[] = $storeWebsiteId;
            }

            $productPrice = false;
            $productVisibility = false;
            $productStatus = false;
            $websiteId = $store->getWebsiteId();
            $defaultStoreWebsite = $store->getWebsite()->getDefaultStore() ?: $store;
            $defaultCurrencyCode = $defaultStoreWebsite->getDefaultCurrencyCode();
            $hideNewProducts = $this->scopeConfig->getValue('epicor_comm_field_mapping/stk_mapping/hide_new_products',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

            if (isset($currencies[$defaultCurrencyCode]['base_price']) && (
                    $this->_baseCurrencyIncluded || (!$this->_baseCurrencyIncluded && $baseCurrency != $defaultCurrencyCode) || $storeCount == 1
                )
            ) {
                $productPrice = $currencies[$defaultCurrencyCode]['base_price'];
                $productCost = $currencies[$defaultCurrencyCode]['cost_price'];
                $productStatus = $enabled;
            } else {
                if ($this->_processingChildren) {
                    $productStatus = $enabled;
                    $productVisibility = $notVisible;
                }
            }

            if (!$this->_processingChildren) {
                if (isset($currencies[$defaultCurrencyCode]['base_price'])) {
                    if (!$forSale || (!$this->_exists() && $hideNewProducts)) {
                        $productVisibility = $notVisible;
                    } else {
                        $productVisibility = $visible;
                    }
                }
            }

            if ($productPrice !== false) {
                $lowestPriceData["price"] = $productPrice;
            }

            //Skip with configurable
            if ($priceUpdate && $productPrice !== false && !$this->_processingConfigurable) {
                $this->_setAttribute($loadSku, 'price', $productPrice, $store->getId());
                $this->_setAttribute($loadSku, 'cost', $productCost, $store->getId());
            }

            if ($productPrice !== false && $this->_processingConfigurable) {
                if (!$configLowestProductPrice) {
                    $configLowestProductPrice = $productPrice;
                }
                if ($productPrice < $configLowestProductPrice) {
                    $configLowestProductPrice = $productPrice;
                }
            }

            /**
             *  simple product validate visibility
             *  if require to mapped
             *  with Configurable product
             */
            if ($this->_isConfigurable && !$this->_processingConfigurable) {
                $erpChildVisibility = $erpData->getProductVisibility();
                $DefaultVisibilityConfigChild = $hideNewProducts = $this->scopeConfig->getValue('epicor_comm_field_mapping/stk_mapping/child_visibility',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());
                if ($erpChildVisibility && array_key_exists($erpChildVisibility, $this->_productVisibility)) {
                    $productVisibility = $this->_productVisibility[$erpChildVisibility];
                } else {
                    $productVisibility = $DefaultVisibilityConfigChild;
                }
            }
            if ($visibilityUpdate && $productVisibility !== false) {
                $this->_setAttribute($loadSku, 'visibility', $this->_visibilityOptions[$productVisibility], $store->getId());
                //$this->updateVisibilityIndex($productId, $store->getId(), $productVisibility, $this->_exists());
            }

            //no update for configurable
            if ($visibilityUpdate && $productStatus !== false) {
                $this->_setAttribute($loadSku, 'status', $productStatus, $store->getId());
                if ($productStatus == $enabled && !in_array($websiteId, $websites)) {
                    $websites[] = $websiteId;
                }
            }
        }

        //Pricing SKU and config price
        if ($productPrice !== false && $this->_processingConfigurable) {
            $pricingSku = null;
            $parentErpData = $erpData->getParent();
            $configLowerPrice = isset($this->_productExitingDataArray[$loadSku]['ecc_configurable_part_price']) ? $this->_productExitingDataArray[$loadSku]['ecc_configurable_part_price'] : false;
            if (!$configLowerPrice) {
                $configLowerPrice = 0;
            }

            //pricing sku and set lowest price or associated product
            if ($configLowestProductPrice < $configLowerPrice || $configLowerPrice === 0) {
                $this->_setAttribute($loadSku, 'ecc_configurable_part_price', $configLowestProductPrice,
                    $store->getId());
                $pricingSku = $erpData->getProductCode();
            }

            if ($parentErpData->getPricingProductCode()) { // available with XMl
                $pricingProductCode = $parentErpData->getPricingProductCode();
                $prodObj = $this->catalogProductFactory->create();
                $validateProduct = $prodObj->getIdBySku($pricingProductCode);
                if ($validateProduct || $pricingProductCode == $erpData->getProductCode()) {
                    $pricingSku = $pricingProductCode;
                }
            }

            if ($pricingSku) {
                $this->_setAttribute($loadSku, 'ecc_pricing_sku', $pricingSku);
            }
        }

        /* set not visible for the store which is not matched */
        $productStoreIds = $this->_loadStoresFromCompanyBranding();
        $visibleStoresIds = array_keys($visibleStores);
        $resetStoreId = array_merge(array_diff($productStoreIds, $visibleStoresIds),
            array_diff($visibleStoresIds, $productStoreIds));
        if ($productStoreIds != array_diff($productStoreIds, $visibleStoresIds)) {
            foreach ($resetStoreId as $store) {
                if ($visibilityUpdate) {
                    $this->_setAttribute($loadSku, 'visibility', $this->_visibilityOptions[$notVisible], $store);
                    /* product status is handled in website scope cannot handle in store view level */
                    //$this->_setAttribute($product->getId(), 'status', $disabled, $store);
                }
            }
        }
        $diff = array_merge(array_diff($visibileWebsiteIds, [1]),
            array_diff([1], $visibileWebsiteIds));
        if (!empty($diff)) {
            $this->_setStaticAttribute($loadSku, 'website_ids', $websites);
        }
    }

    /**
     * Updates Product Locations
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _processLocations($erpData, $loadSku)
    {
        if (!$this->isUpdateable('locations_update', $this->_exists())) {
            return;
        }
        $helper = $this->commLocationsHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Locations */

        $company = $this->getCompany();
        $stores = $this->_loadStores();
        $locations = $this->_getGroupedData('locations', 'location', $erpData);
        $updateFlags = [];
        $updateFlags = array(
            'stock_status' => $this->isUpdateable('location_stock_status', $this->_exists()),
            'free_stock' => $this->isUpdateable('location_free_stock', $this->_exists()),
            'minimum_order_qty' => $this->isUpdateable('location_minimum_order_qty', $this->_exists()),
            'maximum_order_qty' => $this->isUpdateable('location_maximum_order_qty', $this->_exists()),
            'lead_time_days' => $this->isUpdateable('location_lead_time_days', $this->_exists()),
            'lead_time_text' => $this->isUpdateable('location_lead_time_text', $this->_exists()),
            'supplier_brand' => $this->isUpdateable('location_supplier_brand', $this->_exists()),
            'tax_code' => $this->isUpdateable('location_tax_code', $this->_exists()),
            'manufacturers' => $this->isUpdateable('location_manufacturers', $this->_exists()),
            'currencies' => $this->isUpdateable('location_pricing', $this->_exists()),
        );


        $productId = isset($this->_productExitingDataArray[$loadSku]['entity_id']) ?
            $this->_productExitingDataArray[$loadSku]['entity_id'] : 0;
        if ($productId) {
            $currentLocations = $this->getLocationsData($productId);
            $exitingLocations = [];
            if (is_array($currentLocations)) {
                foreach ($currentLocations as $location) {
                    $exitingLocations[$location['location_code']] = $location['id'];
                    $tmplocation = $location;
                    unset($tmplocation['company']);
                    unset($tmplocation['id']);
                    $this->_locations[$location['location_code']] = $tmplocation;
                }
            }
        }
        $newLocations = array();
        foreach ($locations as $location) {
            $country = $location->getCountry();
            $_location = $helper->checkAndCreateLocation($location->getLocationCode(), $company, $stores, $country);
            $_locationCode = is_null($_location) ? $location->getLocationCode() : $_location->getCode();
            $_locationData = $this->_getLocationData($location);
            if (isset($_locationData['location_code'])) {
                $_locationData['location_code'] = $_locationCode;
            }
            $locationModel = $this->setLocationData($loadSku, $_locationCode, $_locationData, $this->_storesDefaultCurrency,
                $updateFlags);
            //$locationModel->save();
            isset($exitingLocations[$_locationCode]) ? $locationModel['id'] = $exitingLocations[$_locationCode] : false;
            $newLocations[$_locationCode] = $locationModel;
        }
        if ($productId) {
            $deleteLocation = [];
            if (is_array($currentLocations)) {
                foreach ($currentLocations as $location) {
                    /* @var $location \Epicor\Comm\Model\Location\Product */
                    // Don't remove any location from Configurable product
                    if (!isset($newLocations[$location['location_code']])
                        && !$this->_processingConfigurable
                        && $company == $location['company']
                    ) {
                        $deleteLocation[] = ['location_code = ?' => $location['location_code'],
                            'product_id = ?' => $productId];
                    }
                }
            }
        }

        if (!empty($deleteLocation)) {
            $this->_productDataArray[$loadSku]['delete_locations'] = $deleteLocation;
        }

        if (!empty($newLocations)) {
            $this->_productDataArray[$loadSku]['new_locations'] = $newLocations;
        }
    }


    /**
     * Sets the data for a location
     *
     * @param integer $productId
     * @return Location\Product|mixed
     */
    public function getLocationsData($productId)
    {
        if ($productId) {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('ecc_location_product');
            $sql = $connection->select()
                ->from(['main_table' => $table], ['id', 'product_id', 'location_code', 'stock_status', 'free_stock',
                    'minimum_order_qty', 'maximum_order_qty', 'lead_time_days', 'lead_time_text', 'supplier_brand',
                    'tax_code', 'manufacturers'])
                ->joinLeft(
                    array(
                        'location_info' => $connection->getTableName('ecc_location')
                    ), 'location_info.code = main_table.location_code', array(
                        'company'
                    )
                )
                ->where('product_id = ?', $productId);
        }
        return $connection->fetchAll($sql);

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
    public function setLocationData($loadSku, $locationCode, $data, $storesdefaultCurrency = array(), $canUpdate = array())
    {
        $newStkLocation = false;
        if (isset($this->_locations[$locationCode])) {
            $location = $this->_locations[$locationCode];
        } else {
            $location = [];
            $newStkLocation = true;
        }
        $productId = isset($this->_productExitingDataArray[$loadSku]['entity_id']) ?
            $this->_productExitingDataArray[$loadSku]['entity_id'] : 0;
        $location['product_id'] = $productId;
        $location['location_code'] = $locationCode;

        foreach ($canUpdate as $key => $value) {
            if ($value || $newStkLocation) {
                if ($key == "currencies") {
                    $location['currencies'] = $this->setCurrencies($productId, $locationCode, $data[$key], $storesdefaultCurrency);
                } elseif ($key == "manufacturers") {
                    $location[$key] = serialize($data[$key]);
                } else {
                    $location[$key] = $data[$key];
                }
            } else {
                if (isset($location[$key])) {
                    $location[$key] = $location[$key];
                }
            }
        }
        $this->_locations[$locationCode] = $location;
        return $location;
    }

    /**
     * Sets the currencies for this product
     *
     * @param array $currencies
     * @param array $storesdefaultCurrency
     */
    public function setCurrencies($productId, $locationCode, $currencies, $storesdefaultCurrency = array())
    {
        $newCurrencies = array();

        $currentCurrencies = $this->getCurrencies($productId, $locationCode);
        $exitingCurrencies = [];
        if (is_array($currentCurrencies)) {
            foreach ($currentCurrencies as $currencie) {
                $exitingCurrencies[$currencie['currency_code']] = $currencie['id'];
            }
        }
        if ($currencies) {
            foreach ($currencies as $currency) {
                if (!empty($storesdefaultCurrency)) {
                    if (in_array($currency->getCurrencyCode(), $storesdefaultCurrency)) {
                        $newCurrencyData = $this->setCurrencyData($productId, $locationCode, $currency->getCurrencyCode(), $currency);
                        $_currencyCode = $currency->getCurrencyCode();
                        isset($exitingCurrencies[$_currencyCode]) ? $newCurrencyData['id'] = $exitingCurrencies[$_currencyCode] : false;
                        $newCurrencies[$currency->getCurrencyCode()] = $newCurrencyData;
                    }
                } else {
                    $newCurrencyData = $this->setCurrencyData($productId, $locationCode, $currency->getCurrencyCode(), $currency);
                    $_currencyCode = $currency->getCurrencyCode();
                    isset($exitingCurrencies[$_currencyCode]) ? $newCurrencyData['id'] = $exitingCurrencies[$_currencyCode] : false;
                    $newCurrencies[$currency->getCurrencyCode()] = $newCurrencyData;
                }
            }
        }

        $deleteCurrencies = [];
        foreach ($currentCurrencies as $currency) {
            /* @var $currency \Epicor\Comm\Model\Location\Product\Currency */
            if (!isset($newCurrencies[$currency['currency_code']])) {
                $deleteCurrencies[] = ['currency_code = ?' => $currency['currency_code'],
                    'product_id = ?' => $productId, 'id = ?' => $currency['id'], 'location_code = ?' => $currency['location_code']];
            }
        }

        if (!empty($deleteCurrencies)) {
            $newCurrencies['delete_currencies'] = $deleteCurrencies;
        }

        return $newCurrencies;
    }


    /**
     * Sets the data for a currency
     *
     * @param string $currencyCode
     * @param \Epicor\Common\Model\Xmlvarien $data
     */
    public function setCurrencyData($productId, $locationCode, $currencyCode, $data)
    {
        /* @var $currency \Epicor\Comm\Model\Location\Product\Currency */
        if (isset($this->_currencies[$currencyCode])) {
            $currency = $this->_currencies[$currencyCode];
        } else {
            $currency = [];
        }

        $currency['product_id'] = $productId;
        $currency['location_code'] = $locationCode;
        $currency['currency_code'] = $currencyCode;
        $currency['base_price'] = $data->getBasePrice();
        $currency['cost_price'] = $data->getCostPrice();

        $this->_currencies[$currencyCode] = $currency;
        return $currency;
    }

    /**
     * Gets all location data for this product
     *
     * @return array
     */
    public function getCurrencies($productId, $locationCode)
    {
        if ($productId) {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('ecc_location_product_currency');
            $sql = $connection->select()
                ->from(['main_table' => $table], ['currency_code', 'id', 'location_code'])
                ->where('product_id = ?', $productId)
                ->where('BINARY location_code = (?)', $locationCode);
            return $connection->fetchAll($sql);
        }
        return [];
    }

    /**
     * Updates Meta Information
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _processMetaInformation($erpData, $sku)
    {
        if (!$this->isUpdateable('metainformation_update', $this->_exists())) {
            return;
        }

        $metaInformation = $erpData->getMetaInformation();
        if ($metaInformation instanceof \Epicor\Common\Model\Xmlvarien) {
            $data = $metaInformation->getData();

            if (isset($data['title'])) {
                $this->_setAttribute($sku, 'meta_title', $data['title']);
            }
            if (isset($data['keywords'])) {
                $this->_setAttribute($sku, 'meta_keyword', $data['keywords']);
            }
            if (isset($data['description'])) {
                $this->_setAttribute($sku, 'meta_description', $data['description']);
            }
        }
    }

    /**
     * Get the data for a location (from STK / MSQ response)
     *
     * @param string $locationCode
     * @param Epicor_Common_Model_Xmlvarien $data
     */
    protected function _getLocationData($data)
    {
        /* @var $location \Epicor\Comm\Model\Location\Product */

        $location = array(
            'location_code' => $data->getLocationCode(),
            'stock_status' => $data->getStockStatus(),
            'free_stock' => $data->getFreeStock(),
            'minimum_order_qty' => $data->getMinimumOrderQty(),
            'maximum_order_qty' => $data->getMaximumOrderQty(),
            'supplier_brand' => $data->getSupplierBrand(),
            'tax_code' => $data->getTaxCode(),
            'lead_time_days' => $data->getLeadTime() ? $data->getLeadTime()->getDays() : '',
            'lead_time_text' => $data->getLeadTime() ? $data->getLeadTime()->getText() : '',
        );

        $manufacturerData = array();
        if ($data->getManufacturers()) {
            $manufacturers = $data->getManufacturers()->getasarrayManufacturer();

            foreach ($manufacturers as $manufacturer) {
                $att = $manufacturer->getData('_attributes');

                $manufacturerData[] = array(
                    'primary' => ($att) ? $att->getPrimary() : 'N',
                    'name' => $manufacturer->getName(),
                    'product_code' => $manufacturer->getProductCode()
                );
            }
        }

        $location['manufacturers'] = $manufacturerData;

        $currencies = array();

        if ($data->getCurrencies()) {
            $currencies = $data->getCurrencies()->getasarrayCurrency();
        }

        $location['currencies'] = $currencies;

        return $location;
    }

    /**
     * Changes a products type and removes any data that needs to be done for the change
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $newType
     */
    protected function _changeProductType($loadSku, $newType)
    {
        $oldType = $this->_productExitingDataArray[$loadSku]['type_id'];
        $productId = isset($this->_productExitingDataArray[$loadSku]['entity_id']) ?
            $this->_productExitingDataArray[$loadSku]['entity_id'] : 0;
        $connection = $this->resourceConnection->getConnection();
        if ($oldType == 'grouped') {
            if ($productId) {
                $exitingChildProducts = $this->getLinkProducts($productId);
                if (!empty($exitingChildProducts)) {
                    foreach ($exitingChildProducts as $sku => $data) {
                        $this->_productDataArray['delete_products'][$sku] = ['sku' => $sku];
                    }
                }
            }
        } else if ($oldType == 'bundle') {
            // delete all bundle option of the product
            $connection->delete(
                $connection->getTableName('catalog_product_bundle_option'),
                ['parent_id = ?' => $productId]
            );
        }

        $where = ['entity_id=?' => $productId];
        $data = ['type_id' => $newType];
        $table = $connection->getTableName('catalog_product_entity');
        $this->updateData(
            $table,
            $data,
            $where
        );
        $this->_newAttribute['product_typ_changed'] = true;
        $this->_indexProducts[] = $loadSku;
    }

    /**
     * Updates product attributes
     *
     * Main DB interaciton goes on in here
     */
    protected function _updateAttributes()
    {
        foreach ($this->_attributesToUpdate as $storeId => $product) {
            foreach ($product as $productId => $attData) {
                foreach ($attData as $code => $value) {
                    if (array_key_exists($code, $this->_selectValues)) {
                        // process select and multiselect values
                        $this->_processMutiSelectAttribute($productId, $code, $value);
                    }
                }
            }
        }
        $this->_attributesToUpdate = array();
        $this->_staticAttributesToUpdate = array();
    }

    /**
     * Checks if an attribute is updateable and updates it if so
     *
     * @param integer $productId
     * @param string $code
     * @param mixed $value
     * @param integer $storeId
     */
    protected function _checkAndSetAttribute($productId, $code, $value, $storeId = null)
    {
        $flagBase = isset($this->_updateConfMap[$code]) ? $this->_updateConfMap[$code] : $code;

        if ($this->isUpdateable($flagBase . '_update', $this->_exists())) {
            $this->_setAttribute($productId, $code, $value, $storeId);
        }
    }

    /**
     * @param $productId
     * @param $code
     * @param $value
     * @param null $storeId
     */
    protected function _checkAndSetEccBrand($productId, $code, $value, $storeId = null)
    {
        $attrExists = $this->attributeCheck->isAttributeExists($code);

        if ($attrExists) {
            $flagBase = isset($this->_updateConfMap[$code]) ? $this->_updateConfMap[$code] : $code;

            if ($this->isUpdateable($flagBase . '_update', $this->_exists())) {
                $this->_setAttribute($productId, $code, $value, $storeId);
            }
        }
    }

    /**
     * @param $productId
     * @param $value
     * @param null $storeId
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function _checkAndSetEccBrandD($productId, $value, $storeId = null)
    {
        $code = 'ecc_brand';
        $eccbrandCode = 'ecc_brand_updated';
        $flagBase = isset($this->_updateConfMap[$code]) ? $this->_updateConfMap[$code] : $code;

        if ($this->isUpdateable($flagBase . '_update', $this->_exists())) {
            if ($value == '' || $value == null) {
                $value = null;
            } else {
                $result = $this->attributeOptions->getOptionLabelValue($eccbrandCode, $value);
                if (empty($result)) {
                    $this->attributeOptions->addAttributeOption($eccbrandCode, $value);
                }
            }
            $this->_setAttribute($productId, $eccbrandCode, $value, $storeId);
        }
    }

    /**
     * Sets a static attribute for update
     *
     * @param integer $productId
     * @param string $code
     * @param mixed $value
     */
    protected function _setStaticAttribute($loadSku, $code, $value)
    {
        if (!isset($this->_staticAttributesToUpdate[$loadSku])) {
            $this->_staticAttributesToUpdate[$loadSku] = array();
        }
        $this->_productExitingDataArray[$loadSku][$code] = $value;
        $this->_staticAttributesToUpdate[$loadSku][$code] = $value;
    }

    /**
     * Sets attributes for saving
     *
     * @param integer $productId
     * @param string $code
     * @param mixed $value
     * @param integer $storeId
     */
    protected function _setAttribute($loadSku, $code, $value, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        if (!isset($this->_attributesToUpdate[$storeId])) {
            $this->_attributesToUpdate[$storeId] = array();
        }

        if (!isset($this->_attributesToUpdate[$storeId][$loadSku])) {
            $this->_attributesToUpdate[$storeId][$loadSku] = array();
        }

        if (in_array($code, $this->_serialize)) {
            $value = serialize($value);
        }

        $this->_productDataArray[$loadSku][$code] = $value;
        $this->_attributesToUpdate[$storeId][$loadSku][$code] = $value;
        $this->_attributesToUpdateStore[$storeId][$loadSku][$code] = $value;
    }

    /**
     * checks whether the produc exists
     *
     * @return boolean
     */
    protected function _exists()
    {
        return $this->_processingChildren ? $this->_childExists : $this->_exists;
    }

    /**
     * processes timing checkpoint
     *
     * @param string $label
     */
    protected function _processCheckpoint($label = '', $timer = true)
    {
        $now = microtime(true);
        $total = round($now - $this->_startTime, 6);
        $checkpoint = round($now - $this->_lastCheckPointTime, 6);
        if ($this->getConfigFlag('debugperformance')) {
            if ($timer) {
                $this->logger->log(200, $label . ' | Time: ' . $total . 's | Checkpoint:' . $checkpoint . 's');
            } else {
                $this->logger->log(200, $label);
            }
        }
        $this->_lastCheckPointTime = $now;
    }

    public function beforeProcessAction()
    {
        $this->_productsMapping();
        parent::beforeProcessAction();
        //  $this->_disableIndexing();
    }

    public function afterProcessAction()
    {
        //  $this->_resetIndexing();
        //$this->_index();
        $this->_processCheckpoint('Finish');
        $this->_processCheckpoint('', false);
        parent::afterProcessAction();
    }

    /**
     * Indexes the product, depending on config
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _index()
    {
        $this->_processCheckpoint('Before Reindex');
        $adapter = $this->resourceConnection->getConnection();
        /* @var $adapter \Epicor\Comm\Model\ResourceModel\Indexer */
        $adapter->beginTransaction();

        try {
            // INDEX PRODUCTS
            $products = array_unique($this->_indexProducts);
            // $nonindexProducts = array_unique($this->_nonindexProducts);
            // $indexproducts = array_diff($products, $nonindexProducts);
            $indexproducts = $products;
            $productHelper = $this->commProductHelper->create();
            /* @var $productHelper \Epicor\Comm\Helper\Product */

            // foreach ($products as $productId) {
            $productHelper->reindexProductById($indexproducts);
            //}

            $adapter->commit();
        } catch (\Exception $e) {
            $adapter->rollback();
            $this->setStatusDescription('Indexing failed, please manually re-index' . $e->getMessage());
        }

        $this->_processCheckpoint('After Reindex');
    }

    /**
     * Updates an existing dropdown with supplied value
     *
     * @param
     * $product - current product
     * $code - attribute code
     * $value - attribute value
     */
    protected function _processMutiSelectAttribute($productId, $code, $values)
    {
        // return true;
        $codeValues = $this->_selectValues[$code];
        $separator = $this->_erpMappingAttributes[$code]['separator'];
        $returnValue = false;
        $attributeOptions = $this->eavEntityAttributeSourceTableFactory->create();

        //loop is for when multiple options for same attribute are supplied (eg select)
        foreach ($codeValues as $codeValue) {
            $attribute = $this->eavEntityAttributeFactory->create()->load($codeValue['id']);
            $attributeOptions->setAttribute($attribute);
            $options = $attributeOptions->getAllOptions(false);
            $optionKeys = array();
            foreach ($options as $option) {
                $optionKeys[$option['label']] = $option['value'];
            }

            $valuesArray = $separator ? explode($separator, $values) : array($codeValue['value']);
            $finalValue = false;
            foreach ($valuesArray as $value) {
                //don't add empty value as option
                if (!$value) {
                    continue;
                }
                $finalValue[] = $value;
                if (!array_key_exists($value, $optionKeys)) {
                    $newOption['label'] = $value;
                    /** @var \Magento\Eav\Api\Data\AttributeOptionInterface $optionDataObject */
                    $optionDataObject = $this->optionDataFactory->create();
                    $this->dataObjectHelper->populateWithArray(
                        $optionDataObject, $newOption, '\Magento\Eav\Api\Data\AttributeOptionInterface'
                    );
                    $this->attributeOptionManagement->add(
                        \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $code, $optionDataObject
                    );
                    $returnValue = true;
                }
            }
        }
        if ($returnValue) {
            if ((is_array($this->_processNewattribute) && !in_array($code, $this->_processNewattribute)) ||
                !$this->_processNewattribute)
                $this->_processNewattribute[] = $code;
        }
        if ($this->_erpMappingAttributes[$code]['input_type'] == 'multiselect') {
            if (is_array($valuesArray)) {
                if ($finalValue) {
                    $value = implode('ecc-multi-sep', $finalValue);
                } else {
                    $value = '';
                }
            } else {
                $value = $valuesArray;
            }
            $this->_setAttribute($productId, $code, $value);
        }
    }

    /**
     * Save values for attributes if required type
     *
     * @param
     * $attributeModel - existing attribute model
     * $code - attribute code
     * $value - attribute value
     */
    protected function _saveAttributeValues($attributeModel, $code, $value)
    {

        //only needed for select or multiselect atm
        $requiredArray = array('select', 'multiselect');
        if (in_array($attributeModel['frontend_input'], $requiredArray)) {
            $this->_selectValues[$code][] = array('id' => $attributeModel['attribute_id'], 'value' => $value);
        }
    }

    /**
     * Save values for attributes if required type
     *
     * @param
     * $code - attribute code
     * $description - attribute description
     * $stkAtttributeDetails -  all attribute details
     */
    protected function _setNewAttributeValues($code, $description, $stkAttributeDetails)
    {
        $specifiedAttributeValues = array();
        $attributeTable = $this->_erpMappingAttributes[$code];
        //only apply settings if object appears on attribute table
        if ($attributeTable) {
            $specifiedAttributeValues = array(
                'input' => $attributeTable['input_type']
//            , 'searchable' => $attributeTable['quick_search']
            ,
                'is_visible_in_advanced_search' => $attributeTable['is_visible_in_advanced_search']
            ,
                'is_searchable' => $attributeTable['is_searchable']
            ,
                'is_comparable' => $attributeTable['is_comparable']
            ,
                'is_filterable' => $attributeTable['is_filterable']
            ,
                'is_filterable_in_search' => $attributeTable['is_filterable_in_search']
            ,
                'is_used_for_promo_rules' => $attributeTable['is_used_for_promo_rules']
            ,
                'is_html_allowed_on_front' => $attributeTable['is_html_allowed_on_front']
            ,
                'is_visible_on_front' => $attributeTable['is_visible_on_front']
            ,
                'used_in_product_listing' => $attributeTable['used_in_product_listing']
            ,
                'used_for_sort_by' => $attributeTable['used_for_sort_by']
//            , 'visible_in_advanced_search' => $attributeTable['advanced_search']
//            , 'filterable' => $attributeTable['use_in_layered_navigation']
//            , 'filterable_in_search' => $attributeTable['search_results']
//            , 'visible_on_front' => $attributeTable['visible_on_product_view']
//            , 'is_configurable' => $attributeTable['use_for_config']
//            , 'user_defined' => true,
//            'backend' => 'eav/entity_attribute_backend_array'
            );

            switch ($attributeTable['input_type']) {
                case 'text':
                    $specifiedAttributeValues['type'] = 'varchar';
                    break;
                case 'textarea':
                    $specifiedAttributeValues['is_html_allowed_on_front'] = true;
                    $specifiedAttributeValues['type'] = 'text';
                    break;
                case 'select':
                    $specifiedAttributeValues['option']['values'] = array($stkAttributeDetails->getValue());
                    $specifiedAttributeValues['type'] = 'int';
                    $specifiedAttributeValues['source'] = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                    break;
                case 'multiselect':
                    $specifiedAttributeValues['type'] = 'text';
                    $specifiedAttributeValues['backend'] = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
                    $separator = $attributeTable['separator'];
                    if ($separator) {
                        $optionArray = explode($separator, $stkAttributeDetails->getValue());
                        $options = array();
                        foreach ($optionArray as $option) {
                            //don't save any empty elements on array (ie if separator is used at end of values)
                            if ($option) {
                                $options["value"][$option] = array($option);
                            }
                        }
                        $specifiedAttributeValues['option'] = $options;
                    }
                    break;
                case 'boolean':
                    $specifiedAttributeValues['type'] = 'int';
                    $specifiedAttributeValues['source'] = 'eav/entity_attribute_source_boolean';
                    $specifiedAttributeValues['default'] = 0;
                    break;
                case 'date':
                    $specifiedAttributeValues['type'] = 'datetime';
                    $specifiedAttributeValues['backend'] = 'eav/entity_attribute_backend_datetime';
                    $specifiedAttributeValues['frontend'] = 'eav/entity_attribute_frontend_datetime';
                    break;
                case 'price':
                    $specifiedAttributeValues['type'] = 'decimal';
                    $specifiedAttributeValues['backend'] = 'catalog/product_attribute_backend_price';
                    break;
                default:
                    break;
            }
        }
        $standardAttributes = array(
            'group' => ''
        ,
            'label' => $description
        ,
            'type' => 'varchar'
        ,
            'input' => 'text'
        ,
            'required' => false
        ,
            'user_defined' => true
        ,
            'searchable' => false
        ,
            'filterable' => false
        ,
            'comparable' => true
//        , 'visible_on_front' => $attributeTable['visible_on_product_view']
        ,
            'is_visible_on_front' => $attributeTable['is_visible_on_front']
        ,
            'visible_in_advanced_search' => false
        );
        return array_merge($standardAttributes, $specifiedAttributeValues);
    }

    /**
     * Determines is the value passed is deemed equivalent to true
     *
     * @param type $value
     *
     * "true", 1, Y, Yes, > 0
     *
     * @return boolean
     */
    private function isTrue($value)
    {
        $value = strtolower($value);

        $isTrue = false;

        $trueValues = array(
            'true',
            1,
            'y',
            'yes'
        );

        if (
            in_array($value, $trueValues) ||
            (is_numeric($value) && (int)$value > 0)
        ) {
            $isTrue = true;
        }

        return $isTrue;
    }

    /**
     * Construct array of data values to be used for creating new product attribute.
     *
     * @param
     * $model - \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     * $code - attribute code
     * $requiredAttributeFields -  all attribute details in array format
     * @return array
     */
    protected function dataForAddAttribute($model, $code, $requiredAttributeFields)
    {

        $data = array();
        $data['attribute_code'] = $code;
        $data['frontend_label'] = array($requiredAttributeFields['label']);
        $data['frontend_input'] = $requiredAttributeFields['input'];
        $data['is_user_defined'] = $requiredAttributeFields['user_defined'];
        $data['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
            $data['frontend_input']
        );
        $data['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
            $data['frontend_input']
        );
        $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
        $data += [
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'is_comparable' => $requiredAttributeFields['comparable']
        ];

        //save other attribute options - use default if required
        $options = $this->_erpMappingAttributes[$code] ?? $this->_attributeDefaultValues;
        foreach ($options as $key => $value) {
            $data[$key] = $value;
        }
        if (in_array($requiredAttributeFields['input'], ['text', 'textarea', 'texteditor', 'date'])) {
            $data['is_filterable'] = 0;
            $data['is_filterable_in_search'] = 0;
        }

        return $data;
    }

    /**
     * Return attribuet set grop id
     */
    public function getAttrGroupId($attrId, $setId)
    {
        if (!$this->_attributegroup) {
            $productHelper = $this->commProductHelper();
            $this->_attributegroup = $productHelper->getAttributeGroupId($attrId, $setId);
        }
        $getAttributeGroupIdValue = isset($this->_attributegroup[$attrId . 'setId' . $setId]) ? $this->_attributegroup[$attrId . 'setId' . $setId] : null;
        return $getAttributeGroupIdValue;
    }

    /**
     * Return attribuet set id
     */
    public function getAttributeId($attributeCode)
    {
        if (!$this->_attributeId) {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('eav_attribute');
            $sql = $connection->select()
                ->from($table)
                ->where('entity_type_id = ?', $this->_productEnityTypeId);
            $attributes = $connection->fetchAll($sql);
            $attributelist = [];
            foreach ($attributes as $item) {
                $attributelist[$item['attribute_code']] = $item['attribute_id'];
            }
            $this->_attributeId = $attributelist;
        }
        $attributeId = isset($this->_attributeId[$attributeCode]) ? $this->_attributeId[$attributeCode] : null;
        return $attributeId;
    }

    public function commProductHelper()
    {
        if (!$this->commProductHelperForStk) {
            $this->commProductHelperForStk = $this->commProductHelper->create();
        }
        return $this->commProductHelperForStk;
    }

    /**
     * check product level and global level configuration for decimal qty
     * @return bool
     */
    public function acceptsDecimalQty()
    {
        $acceptDecimal = false;
        //get global level setting
        $globalLevelDecimalPlaces = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/qtydecimals',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $globalLevel = (!is_null($globalLevelDecimalPlaces)) ? 1 : 0;
        //$productLevelDecimalPlaces = $this->_defaultUom['decimal_places'];
        $productLevel = ((isset($this->_defaultUom['decimal_places']) && !empty($this->_defaultUom['decimal_places'])) && $this->_defaultUom['decimal_places'] > 0) ? 1 : 0;
        if ($productLevel) {
            $acceptDecimal = true;
        } elseif (!$productLevel && $globalLevel) {
            $acceptDecimal = true;
        }
        return $acceptDecimal;
    }
}
