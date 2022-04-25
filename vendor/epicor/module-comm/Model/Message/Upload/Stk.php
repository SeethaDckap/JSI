<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Message\Upload;

use Epicor\Comm\Model\Message\Upload;
use Epicor\Comm\Service\AttributeCheck;
use Epicor\Comm\Service\AttributeOptions;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Scope;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link as GroupedProductLink;
use Epicor\Comm\Model\Product as CommProduct;
use Epicor\Comm\Model\Product\Type\Grouped as EpicorProductGrouped;
use Magento\GroupedProduct\Model\Product\Type\Grouped as MagentoProductGrouped;
use Magento\Framework\Exception\NoSuchEntityException;
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
class Stk extends Upload
{
    // Configuration path for the STK default Attribute set
    CONST XML_PATH_STK_DEFAULT_ATTR_SET = 'epicor_comm_field_mapping/stk_mapping/default_attribute_set';

    // Configuration path for New Attributes Visible on Frontend
    CONST XML_PATH_STK_NAVF = 'epicor_comm_field_mapping/stk_mapping/attributes_visible';

    /**
     * @var \Epicor\Comm\Model\Product
     */
    protected $_startTime = 0;
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
    protected $_commErpMappingAttributesFactory = false;
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
    protected $commProductHelperForStk = null;
    protected $_storesDefaultCurrency = array();
    protected $_isConfigurable = false;
    protected $_processingConfigurable = false;

    /**
     * Reference Data
     */
    protected $_productTypes = array(
        'S' => 'simple',
        'C' => 'simple',
        'K' => 'simple',
        'E' => 'bundle'
    );

    protected $_productVisibility = array(
        'I' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
        'C' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
        'S' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
        'B' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
    );

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
        'ecc_manufacturers',
        'ecc_related_documents',
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
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $catalogInventoryStockItemFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\AttributesFactory
     */
    protected $commErpMappingAttributesFactory;

    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $eavConfigFactory;

    /**
     * @var \Magento\Catalog\Setup\CategorySetupFactory
     */
    protected $catalogCategorySetupFactory;

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
     * @var \Magento\Bundle\Model\OptionFactory
     */
    protected $bundleOptionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\ActionFactory
     */
    protected $catalogResourceModelProductActionFactory;

    /**
     * @var \Epicor\Comm\Model\IndexerFactory
     */
    protected $commIndexerFactory;

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
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

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
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface
     */
    protected $customoptions;

    /**
     * @var \Magento\Bundle\Api\Data\LinkInterfaceFactory
     */
    protected $bundleLink;

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
     * @var \Magento\Bundle\Model\OptionRepository
     */
    private $bundleOptionRepository;
    /**
     * @var \Magento\Bundle\Api\Data\OptionInterface
     */
    private $optionInterface;
    /**
     * @var \Magento\Bundle\Api\ProductOptionRepositoryInterface
     */
    private $productOptionRepository;

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

    /**
     * @var \Magento\ConfigurableProduct\Helper\Product\Options\Factory
     */
    protected $optionFactory;

    /**
     * @var AttributeOptions
     */
    private $attributeOptions;

    /**
     * @var AttributeCheck|null
     */
    private $attributeCheck;

    /**
     * Stk constructor.
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
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $catalogInventoryStockItemFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Epicor\Comm\Model\Erp\Mapping\AttributesFactory $commErpMappingAttributesFactory
     * @param \Magento\Eav\Model\ConfigFactory $eavConfigFactory
     * @param \Magento\Catalog\Setup\CategorySetupFactory $catalogCategorySetupFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $eavEntityAttributeFactory
     * @param \Magento\Store\Model\StoreFactory $storeStoreFactory
     * @param \Epicor\Comm\Helper\Locations $commLocationsHelper
     * @param \Magento\Bundle\Model\OptionFactory $bundleOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\ActionFactory $catalogResourceModelProductActionFactory
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     * @param \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $eavEntityAttributeSourceTableFactory
     * @param \Magento\Framework\App\Cache\StateInterface $state
     * @param \Epicor\Comm\Model\GlobalConfig\Config $globalConfig
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $catalogResourceModelEavAttributeFactory
     * @param \Magento\Eav\Model\Entity\Type $eavEntityType
     * @param \Magento\Catalog\Model\Product\OptionFactory $customoptions
     * @param \Magento\Bundle\Api\Data\LinkInterfaceFactory $bundleLink
     * @param \Magento\Eav\Model\Entity\Attribute\Set $eavEntityAttributeSet
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Url $frontendUrlHelper
     * @param \Magento\Eav\Model\AttributeManagement $attributeManagement
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Bundle\Api\ProductOptionRepositoryInterface $productOptionRepository
     * @param \Magento\Bundle\Model\OptionRepository $bundleOptionRepository
     * @param \Magento\Bundle\Api\Data\OptionInterface $optionInterface
     * @param \Epicor\Comm\Model\Erp\Mapping\ProductsFactory $commErpMappingProductsFactory
     * @param \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType
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
        \Magento\CatalogInventory\Model\Stock\ItemFactory $catalogInventoryStockItemFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Epicor\Comm\Model\Erp\Mapping\AttributesFactory $commErpMappingAttributesFactory,
        \Magento\Eav\Model\ConfigFactory $eavConfigFactory,
        \Magento\Catalog\Setup\CategorySetupFactory $catalogCategorySetupFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $eavEntityAttributeFactory,
        \Magento\Store\Model\StoreFactory $storeStoreFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Bundle\Model\OptionFactory $bundleOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\ActionFactory $catalogResourceModelProductActionFactory,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $eavEntityAttributeSourceTableFactory,
        \Magento\Framework\App\Cache\StateInterface $state,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $catalogResourceModelEavAttributeFactory,
        \Magento\Eav\Model\Entity\Type $eavEntityType,
        \Magento\Catalog\Model\Product\OptionFactory $customoptions,
        \Magento\Bundle\Api\Data\LinkInterfaceFactory $bundleLink,
        \Magento\Eav\Model\Entity\Attribute\Set $eavEntityAttributeSet,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Url $frontendUrlHelper,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Bundle\Api\ProductOptionRepositoryInterface $productOptionRepository,
        \Magento\Bundle\Model\OptionRepository $bundleOptionRepository,
        \Magento\Bundle\Api\Data\OptionInterface $optionInterface,
        \Epicor\Comm\Model\Erp\Mapping\ProductsFactory $commErpMappingProductsFactory,
        \Magento\ConfigurableProduct\Helper\Product\Options\Factory $optionFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableType,
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
        $this->catalogInventoryStockItemFactory = $catalogInventoryStockItemFactory;
        $this->eavConfig = $eavConfig;
        $this->commErpMappingAttributesFactory = $commErpMappingAttributesFactory;
        $this->eavConfigFactory = $eavConfigFactory;
        $this->catalogCategorySetupFactory = $catalogCategorySetupFactory;
        $this->eavEntityAttributeFactory = $eavEntityAttributeFactory;
        $this->storeStoreFactory = $storeStoreFactory;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->bundleOptionFactory = $bundleOptionFactory;
        $this->catalogResourceModelProductActionFactory = $catalogResourceModelProductActionFactory;
        $this->commIndexerFactory = $context->getCommIndexerFactory();
        $this->commProductHelper = $commProductHelper;
        $this->eavEntityAttributeSourceTableFactory = $eavEntityAttributeSourceTableFactory;
        $this->catalogResourceModelEavAttributeFactory = $catalogResourceModelEavAttributeFactory;
        $this->commProductHelper = $context->getCommProductHelper();
        $this->_cacheState = $state;
        $this->globalConfig = $globalConfig;
        $this->catalogResourceModelEavAttributeFactory = $catalogResourceModelEavAttributeFactory;
        $this->eavEntityType = $eavEntityType;
        $this->eavEntityAttributeSet = $eavEntityAttributeSet;
        $this->urlBuilder = $urlBuilder;
        $this->customoptions = $customoptions;
        $this->bundleLink = $bundleLink;
        $this->filterManager = $filterManager;
        $this->attributeManagement = $attributeManagement;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->productHelper = $productHelper;
        $this->optionDataFactory = $optionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_attributeLabelCache = $context->getCacheManager();
        $this->bundleOptionRepository = $bundleOptionRepository;
        $this->optionInterface = $optionInterface;
        $this->productOptionRepository = $productOptionRepository;
        $this->optionFactory = $optionFactory;
        $this->configurableType = $configurableType;

        $this->commErpMappingProductsFactory = $commErpMappingProductsFactory->create();
        $productsMapping = $this->commErpMappingProductsFactory->getCollection()
            ->addFieldToSelect(['product_sku', 'product_uom'])
            ->getData();
        $uomSeparator = $this->commMessagingHelper->getUOMSeparator();
        $this->mappedProductSkus = array_column($productsMapping, 'product_sku');
        foreach ($productsMapping as $productUoms) {
            $this->mappedProductUoms[] = $productUoms['product_sku'] . $uomSeparator . $productUoms['product_uom'];
        }
        //$this->commProductHelper = $context->getCommProductHelper();

        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->_storeId = $this->storeManager->getStore()->getId();

        //M1 > M2 Translation Begin (Rule p2-6.10)
        //Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_CODE);
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        //M1 > M2 Translation End
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->setConfigBase('epicor_comm_field_mapping/stk_mapping/');
        $this->setMessageType('STK');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_PRODUCT);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_product', true, true);
        $this->attributeOptions = $attributeOptions ?: ObjectManager::getInstance()->get(AttributeOptions::class);
        $this->attributeCheck = $attributeCheck ?: ObjectManager::getInstance()->get(AttributeCheck::class);
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
                $this->_deleteProduct($loadSku);
                $modeDelete = true;
            } else {
                $prodObj = $this->catalogProductFactory->create();
                $productId = $prodObj->getIdBySku($loadSku);
                $product = $prodObj->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)->load($productId);
                /* @var $product \Epicor\Comm\Model\Product */

                $this->_exists = !$product->isObjectNew();
                if ($newCode != null && !$this->_exists) {
                    /* @var $obj Epicor_Comm_Model_Product */
                    $obj = $this->catalogProductFactory->create();
                    $newProductId = $obj->getIdBySku($sku);
                    $product = $obj->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)->load($newProductId);
                    $this->_exists = !$product->isObjectNew();
                    if ($this->_exists) {
                        $this->setMessageSecondarySubject('Renaming ' . $sku . ' to ' . $newCode);
                    }
                } else {
                    $this->setMessageSecondarySubject('Updating ' . $loadSku);
                }
                if ($this->_exists) {
                    $this->_existingProductAttributeSetName = $this->eavEntityAttributeSetFactory->create()->load($product->getAttributeSetId())->getAttributeSetName();
                }
                $this->_stkType = $this->_flags->getType();
                $this->_productType = $this->_productTypes[$this->_flags->getType()];

                if ($this->_stkType == 'E' && $this->isMappedSkuEon($loadSku)) {
                    $this->_stkType = 'S';
                    $this->_productType = $this->_productTypes[$this->_stkType];
                }

                $this->_processCheckpoint('Pre-Process');
                $this->_preProcessData($product);

                if (count($this->_validUoms) > 1) {
                    $this->_childType = $this->_productType;
                    $this->_productType = 'grouped';
                }

                // if not create it

                if (!$this->_exists) {
                    $this->setMessageSecondarySubject('Creating ' . $loadSku);
                    $this->_processCheckpoint('Before Creation');
                    $product = $this->_createBaseProduct($loadSku);
                    $this->_processCheckpoint('After Creation');
                } else {
                    $this->_changeAttributeSet($product);
                }

                $this->_processCheckpoint('Before Updating');
                // updates product attributes
                $this->_processProduct($product, $this->erpData, $this->_productType);
                $this->_processCheckpoint('After Updating');
                if ($this->_isConfigurable) {
                    $this->_processConfigurable($product, $this->erpData);
                }
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

    private function isMappedSkuEon($sku): bool
    {
        if ($productUom = $this->getSkuSeparatedUomString($sku)) {
            return in_array($productUom, $this->mappedProductUoms);
        }

        return false;
    }

    /**
     * Returns a string of the sku and Uom separated by the UOM separator
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

    private function getProductDefaultUomCode()
    {
        if (!$this->productDefaultUomCode && $this->erpData) {
            $unitsOfMeasureGroup = $this->erpData->getUnitOfMeasures();
            $unitOfMeasureItems = $unitsOfMeasureGroup->getUnitOfMeasure();

            $this->productDefaultUomCode = $this->getDefaultUomCode($unitOfMeasureItems);
        }

        return $this->productDefaultUomCode;
    }

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
     */
    protected function _changeAttributeSet($product)
    {
        if ($this->isUpdateable('attributeset_update') && $product->getAttributeSetId() != $this->_attributeSet) {
            $this->_oldAttributeSet = $product->getAttributeSetId();
            $product->setAttributeSetId($this->_attributeSet);
            $this->_setStaticAttribute($product->getId(), 'attribute_set_id', $this->_attributeSet);
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
    protected function _preProcessData($product)
    {
        $this->_preProcessUoms($product);
        $this->_preProcessTax();
        $this->_preProcessWebsites();
        $this->_preProcessAttributeSet();
        $this->_preProcessAttributes();
        $this->_preProcessStoresDefaultCurrencies();
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
     * Process UOM data into a useable array
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _preProcessUoms($product)
    {
        $unitOfMeasures = $this->_getGroupedData('unit_of_measures', 'unit_of_measure', $this->erpData);
// check if product already exists. If so, save UOM filter values as array
        if ($this->_exists) {
            if (is_string($product->getEccUomFilter())) {
                $this->_savedExcludedUom = explode(',', $product->getEccUomFilter());
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

            //M1 > M2 Translation Begin (Rule p2-5.2)
            /*$taxClassColl = Mage::getResourceModel('tax/class_collection')
                ->addFieldToFilter('class_name', $taxCode)
                ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
                ->addFieldToSelect('class_id')
                ->setPageSize(1);*/
            $taxClasses = $this->taxCollectionFactory->create()
                ->addFieldToFilter('class_name', $taxCode)
                ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT)
                ->addFieldToSelect('class_id')
                ->setPageSize(1);
            //M1 > M2 Translation End

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

    protected function _preProcessAttributeSet()
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
    }

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
    }

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

                    if ($delete) {
                        $this->deleteGroupedProductChildIds($product);
                        $this->deleteConfigurableProduct($product);
                        $product->delete();
                        $deleted = true;
                    } else {
                        foreach ($disableWebsites as $website) {
                            /* @var $website Mage_Core_Model_Website */
                            $this->_setAttribute($productId, 'status', $disabled, $website->getDefaultStore()->getId());
                        }

                        foreach ($deleteStores as $store) {
                            /* @var $store Mage_Core_Model_Store */
                            $this->_setAttribute($productId, 'visibility', $notVisible, $store->getId());
                            $this->updateVisibilityIndex($productId, $store->getId(), $notVisible);
                        }
                        $this->deleteProductLocations($product);
                        if ($product->getTypeId() == 'grouped') {
                            $linkedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                            foreach ($linkedProducts as $linkedProduct) {
                                foreach ($disableWebsites as $website) {
                                    /* @var $website Mage_Core_Model_Website */
                                    $this->_setAttribute($linkedProduct->getId(), 'status', $disabled,
                                        $website->getDefaultStore()->getId());
                                }
                                foreach ($deleteStores as $store) {
                                    /* @var $store Mage_Core_Model_Store */
                                    $this->_setAttribute($linkedProduct->getId(), 'visibility', $notVisible,
                                        $store->getId());
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
     * Delete Config Product IF only one associated to them
     *
     * @param CommProduct $product
     * @throws \Exception
     */
    private function deleteConfigurableProduct($product)
    {
        //get all parent's
        if ($product->getTypeId() === "simple") {
            $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
            if ($parentIds) {
                foreach ($parentIds as $oldParentId) {
                    $oldConfigProduct = null;
                    $childs = $this->configurableType->getChildrenIds($oldParentId);
                    if (isset($childs[0])) {
                        $childs = $childs[0];
                    }

                    //Only one associated to configurable then delete product itself
                    if (is_array($childs) && count($childs) <= 1 && array_key_exists($product->getId(), $childs)) {
                        try {
                            $configProduct = $this->_productRepositoryInterface->getById($oldParentId);
                            /** @var CommProduct $configProduct */
                            $this->_productRepositoryInterface->delete($configProduct);
                        } catch (\Exception $e) {
                            throw new \Exception('something went wrong when delete configurable product:' . ' (' . $e->getMessage() . ')');
                        }
                    } else {
                        if (is_array($childs) && count($childs) > 0) {
                            $this->resetConfigurablePricingSku($childs, $oldParentId, $product);
                        }
                    }
                }
            }
        }
    }

    private function resetConfigurablePricingSku($childs = array(), $configurableProductId, $product)
    {
        // Fetch price attribute id from eav_attribute table
        $attrPriceId = null;
        $connection = $this->resourceConnection->getConnection();

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
                    if ($product->getId() != $value['entity_id']) {
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
                    if ($configProduct->getEccPricingSku() != null && $configProduct->getEccPricingSku() == $product->getSku()) {
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

    /**
     * CREATION
     */

    /**
     * Creates the base product for this STK
     *
     * @param string $sku
     *
     * @return \Epicor\Comm\Model\Product
     */
    protected function _createBaseProduct($sku)
    {
        $product = $this->_createProduct($sku, $this->_productType, $this->_stkType);

        return $product;
    }

    /**
     * Creates a basic product with minimal data
     *
     * @param string $sku - SKU for the product
     * @param string $type - type (simple, bundle, grouped etc)
     *
     * @return \Epicor\Comm\Model\Product
     */
    protected function _createProduct($sku, $type, $stkType)
    {
        $product = $this->catalogProductFactory->create();
        /* @var $product \Epicor\Comm\Model\Product */
        $product->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $product->setAttributeSetId($this->_attributeSet);
        $product->setTypeId($type);
        $product->setSku($sku);
        //M1 > M2 Translation Begin (Rule 40)
        //$product->setName($this->erpData->getTitle());
        $product->setName($this->erpData->getTitle() . $sku);
        //M1 > M2 Translation End
        if ($this->commMessagingHelper->create()->checkUomHasSpecialCharacter($sku)) {
            $product->setName($this->erpData->getTitle() . $sku . rand());
        }
        $options = $this->_getGroupedData('options', 'option', $this->erpData);

        if (!empty($options) || $type == 'bundle' || $this->_stkType == 'C') {
            $product->setHasOptions(1);
        }

        if ($type == 'bundle' || $this->_stkType == 'E') {
            $weightType = $this->getConfigFlag('kit_weight_fixed') ? 1 : 0;
            $product->setWeightType($weightType);
            $product->setSkuType(1);
            $product->setPriceView(0);
            $product->setPriceType(1);
            $this->_processBundleOptions($this->erpData, $product);
        }

        //configurable product
        if ($type == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            if ($this->erpData->getParent()->getTitle()) {
                $product->setName($this->erpData->getParent()->getTitle());
            }
        }

        $product->setWebsiteIds($this->_websites);

        $product->save();
        $this->_indexProducts[] = $product->getId();
        $this->_setProductDefaults($product);
        return $product;
    }

    /**
     * Sets basic defaults for the product
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _setProductDefaults(&$product)
    {
// call various update things, like description, tax, stock etc
        $this->_setAttribute($product->getId(), 'price_type', 0);
        $reorder = true;                                                    //  no longer relevant to set to false if stkType = 'C'
        $this->_setAttribute($product->getId(), 'ecc_reorderable', $reorder);

        foreach ($product->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $this->_setAttribute($product->getId(), $mediaAttrCode, 'no_selection');
        }

        $this->_setAttribute($product->getId(), 'ecc_configurator', 0);

        $this->_setAttribute($product->getId(), 'visibility',
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $this->_setAttribute($product->getId(), 'status',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

//        foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
//            //Unset data if object attribute has no value in current store
//            if (!$product->getExistsStoreValueFlag($attribute->getAttributeCode()) && $attribute->getDefaultValue() != NULL
//            ) {
//                $product->setData($attribute->getAttributeCode(), $attribute->getDefaultValue());
//            }
//        }
///$this->_updateAttributes();
    }

    /**
     * Processes the creation / update of child uom products
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param array $children
     */
    protected function _processUomChildren(&$product, $children)
    {
        $uomSeparator = $this->commMessagingHelper->create()->getUOMSeparator();
        $productCode = $product->getSku() . $uomSeparator;
        $linkProducts = array();
        $prices = array();

        $this->_processingChildren = true;

// See note below on singleton as to why we store this data
        $origUOM = $this->erpData->getUomSalesDescription();
        $origCurrencies = $this->erpData->getCurrencies();
        $origAttribute = $this->erpData->getAttributes();
        $origWeight = $this->erpData->getWeight();
        $origDecimalPlaces = $this->erpData->getDecimalPlaces();
        $origPackSize = $this->erpData->getPackSize();

        foreach ($children as $child) {
            $sku = $productCode . $child->getCode();
            $productId = $this->catalogProductFactory->create()->getIdBySku($sku);
            if (!$productId) {
                $childProduct = $this->_createProduct($sku, $this->_childType, $this->_stkType);
                $this->_childExists = false;
            } else {
                $childProduct = $this->catalogProductFactory->create()->load($productId);
                $this->_changeAttributeSet($childProduct);
                $this->_childExists = true;
            }
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

            $this->_setAttribute($childProduct->getId(), 'visibility',
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
            if (in_array($sku, $this->mappedProductSkus) || in_array($sku, $this->mappedProductUoms)) {
                $this->_setAttribute($childProduct->getId(), 'visibility',
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
            }
            $uomErpData = $this->erpData;
            $uomErpData->setUomSalesDescription($child->getCode());
            $uomErpData->setCurrencies($child->getCurrencies());
            $uomErpData->setAttributes($_childAttributes);
            $uomErpData->setWeight($child->getWeight());
            $uomErpData->setDecimalPlaces($child->getDecimalPlaces());
            $uomErpData->setPackSize($child->getDescription());

            $this->_processProduct($childProduct, $uomErpData, $this->_childType);

            //$this->_processAttributes($child, $childProduct);

            $this->_uomProducts[$childProduct->getSku()] = $childProduct;
            $linkProducts[$childProduct->getId()] = array('position' => 0, 'qty' => '');
            $prices[$childProduct->getId()] = $childProduct->getPrice();
        }

        $this->_processingChildren = false;

        $updateUomOrder = $this->isUpdateable('uom_order_update', $this->_exists);

        if ($updateUomOrder) {
            asort($prices);
            $pos = 0;
            foreach ($prices as $id => $price) {
                $linkProducts[$id]['position'] = $pos;
                //M1 > M2 Translation Begin (Rule 41)
                $linkProducts[$id]['product_id'] = $id;
                //M1 > M2 Translation End
                $pos++;
            }
        } else {
            asort($prices);
            foreach ($prices as $id => $price) {
                $linkProducts[$id]['position'] = 0;
                $linkProducts[$id]['product_id'] = $id;
            }
        }

// have to do this otherwise due to the singleton nature of magento, the parent will get the last childs info :(

        $this->erpData->setUomSalesDescription($origUOM);
        $this->erpData->setCurrencies($origCurrencies);
        $this->erpData->setAttributes($origAttribute);
        $this->erpData->setWeight($origWeight);
        $this->erpData->setDecimalPlaces($origDecimalPlaces);
        $this->erpData->setPackSize($origPackSize);

        if ($this->_exists) {
            //M1 > M2 Translation Begin (Rule 42)
            // $originalLinkedProducts = $product->getGroupedLinkCollection()->getItems();
            $originalLinkedProducts = array_values($product->getTypeInstance()->getChildrenIds($product->getId()));
            $originalLinkedProducts = $originalLinkedProducts[0];
            //M1 > M2 Translation End
//delete linked products which are nolonger in the list.
            $this->_processingUOMs = false;
            foreach ($originalLinkedProducts as $originalLinkedProduct) {
                /* @var $originalLinkedProduct \Magento\Catalog\Model\Product\Link */
                if (!array_key_exists($originalLinkedProduct, $linkProducts)) {
                    $childProduct = $this->_productRepositoryInterface->getById($originalLinkedProduct);
                    /* @var $childProduct \Magento\Catalog\Model\Product */
                    if ($this->_deleteProduct($childProduct)) {
                        $this->_processCheckpoint('Deleted Old UOM Product');
                    } else {
                        $linkProducts[$originalLinkedProduct]['position'] = 0;
                        $linkProducts[$originalLinkedProduct]['qty'] = 0;
                    }
                } else {
                    if (!$updateUomOrder) {
                        $linkProducts[$originalLinkedProduct]['position'] = 0;
                    }
                }
            }
        }
        //M1 > M2 Translation Begin (Rule 11)
        //$product->setGroupedLinkData($linkProducts);
        //$link = $product->getLinkInstance();
        //$link->saveGroupedLinks($product);
        $link = $product->getLinkInstance();
        /* @var $link \Magento\Catalog\Model\Product\Link */

        $link->getResource()->saveProductLinks($product->getId(), $linkProducts,
            \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);
        //M1 > M2 Translation End
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
    protected function _processProduct(&$product, $erpData, $type)
    {
        // checks if the product exists
        $this->_processTypeChanges($product, $type);
        if ($type == 'grouped') {
            $this->_processGrouped($product, $erpData);
        } else {
            switch ($this->_stkType) {
                case 'S': //Simple
                    $this->_processSimple($product, $erpData);
                    $this->_processType($product, $erpData, $this->_stkType);
                    break;
                case 'E': // Exploded Kit
                    if ($this->isMappedSkuEon($product->getSku())) {
                        $this->_processSimple($product, $erpData);
                    } else {
                        $this->_processBundle($product, $erpData);
                    }
                    $this->_processType($product, $erpData, $this->_stkType);
                    break;
                case 'C': // Configurator
                    $this->_processConfigurator($product, $erpData);
                    $this->_processType($product, $erpData, $this->_stkType);
                    break;
                case 'K': // Configurator
                    $this->_processConfigurator($product, $erpData);
                    $this->_processType($product, $erpData, $this->_stkType);
                    break;
            }
        }

        $this->_processData($product, $erpData);
        $this->_updateAttributes();


    }

    /**
     * Kinetic Configurator product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processType($product, $erpData, $type)
    {
        //set is configurable
        if ($type == "S" &&
            $this->_productType == "simple" &&
            $erpData->getParent() &&
            $erpData->getParent()->getProductCode()
        ) {
            $this->_isConfigurable = true;
        }

        $this->_setAttribute($product->getId(), 'ecc_product_type', $type);
    }

    /**
     * Does code necessary for when the product type changes
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param string $type
     */
    public function _processTypeChanges(&$product, $type)
    {
        if ($this->_exists() && $type != $product->getTypeId()) {
            $this->_changeProductType($product, $type);
        }

        if (($this->_stkType != 'K' && $this->_stkType != 'C') && $product->getEccConfigurator()) {
            $this->_removeConfiguratorOption($product);
        }

        if (($this->_stkType != 'K' || $this->_stkType != 'C') && $product->getEccConfigurator()) {
            $this->_setAttribute($product->getId(), 'ecc_configurator', 0);
            $this->_setAttribute($product->getId(), 'ecc_reorderable', true);
        }
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
    protected function _processSimple($product, $erpData)
    {
        // not needed at the moment, but may be in future
    }

    /**
     * Grouped product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processGrouped($product, $erpData)
    {
        if (count($this->_validUoms) > 1) {
            $this->_processUomChildren($product, $this->_validUoms);
        }
    }

    /**
     * Bundle product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processBundle($product, $erpData)
    {
        $this->_setAttribute($product->getId(), 'sku_type', 1);
        $this->_setAttribute($product->getId(), 'price_type', 1);

        $this->_processBundleOptions($erpData, $product);
    }

    /**
     * Configurator product specific code
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processConfigurator($product, $erpData)
    {
        $this->_setAttribute($product->getId(), 'ecc_configurator', 1);
        $this->_processConfiguratorOption($product);
    }

    /**
     * UPDATING - GENERIC
     */

    /**
     * Processes the data from the message and builds updates as needed
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     */
    protected function _processData($product, $erpData)
    {
        $id = $product->getId();

        $type = $this->_processingChildren ? $this->_childType : $this->_productType;
        $this->_setAttribute($id, 'ecc_stk_type', $type);

        $newCode = $erpData->getNewProductCode();

        if (!empty($newCode) && $product->getData('sku') !== $newCode) {
            $oldskus = $product->getOldskus() ? explode(',', $product->getOldskus()) : array();
            array_push($oldskus, $product->getSku());
            $oldskuText = implode(',', $oldskus);
            $this->_setAttribute($id, 'ecc_oldskus', $oldskuText);
            $explodeedData = array();
            if ($this->_processingChildren) {
                $uomSeparator = $this->getHelper()->getUOMSeparator();
                $explodeedData = explode($uomSeparator, $product->getData('sku'));
            }
            if (isset($explodeedData[1])) {
                $newCodewithuomSeparator = $newCode . $uomSeparator . $explodeedData[1];
            } else {
                $newCodewithuomSeparator = $newCode;
            }
            $product->setSku($newCodewithuomSeparator);
            $this->_setStaticAttribute($id, 'sku', $newCodewithuomSeparator);
        }

        $currencies = $this->_getCurrencies($erpData);

        $this->_checkAndSetAttribute($id, 'name', $erpData->getTitle());
        $this->_checkAndSetAttribute($id, 'short_description', $erpData->getShortDescription());
        $this->_checkAndSetAttribute($id, 'description', $erpData->getDescription());
        $this->_checkAndSetAttribute($id, 'ecc_default_category_position', $erpData->getProductWeightingCode());

        $googleFeed = $this->_flags->getGoogle();
        $showInGoogle = (strtoupper($googleFeed) === 'Y') ? 1 : 0;
        $this->_checkAndSetAttribute($id, 'ecc_google_feed', $showInGoogle);
        $sb = $erpData->getSupplierBrand();
        $this->_checkAndSetEccBrand($id, 'ecc_brand', $sb);
        $this->_checkAndSetEccBrandD($id, $sb);
        $this->_checkAndSetAttribute($id, 'ecc_manufacturers', $this->_getManufacturers($erpData));
        $this->_setAttribute($id, 'ecc_lead_time', $this->_getLeadTime($erpData, $product));
        $this->_checkAndSetAttribute($id, 'weight', $this->_getWeight($erpData));
        $this->_checkAndSetAttribute($id, 'tax_class_id', $this->_taxClassId);
        $this->_checkAndSetAttribute($id, 'hazard_class', $erpData->getData('hazard_class'));
        $this->_checkAndSetAttribute($id, 'hazard_class_desc', $erpData->getData('hazard_class_desc'));
        $this->_checkAndSetAttribute($id, 'hazard_code', $erpData->getData('hazard_code'));
        $this->_checkAndSetAttribute($id, 'id_number', $erpData->getData('id_number'));
        $this->_checkAndSetAttribute($id, 'supplierpartnumber', $erpData->getData('supplier_part_number'));

        //M1 > M2 Translation Begin (Rule p2-6.6)
        //$baseCurrency = Mage::app()->getBaseCurrencyCode();
        $baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
        //M1 > M2 Translation End
        $basePrice = isset($currencies[$baseCurrency]['base_price']) ? $currencies[$baseCurrency]['base_price'] : "0";
        $costPrice = isset($currencies[$baseCurrency]['cost_price']) ? $currencies[$baseCurrency]['cost_price'] : "0";

        $this->_checkAndSetAttribute($id, 'price', $basePrice);
        $this->_checkAndSetAttribute($id, 'cost', $costPrice);

        //Discontinued & Non Stock Item WSO-7913 & WSO-7913
        $discontinued = $erpData->getData('is_discontinued') == "Y" ? true : false;
        $nonStock = $erpData->getData('is_non_stock') == "Y" ? true : false;
        $this->_checkAndSetAttribute($id, 'is_ecc_discontinued', $discontinued);
        $this->_checkAndSetAttribute($id, 'is_ecc_non_stock', $nonStock);

        if ($this->_processingChildren) {
// have to set this otherwise UOM ordering gets screwed, product is not saved so it's ok here
            $product->setPrice($basePrice);
        }

        $this->_checkAndSetAttribute($id, 'ecc_related_documents', $this->_getRelatedDocuments($erpData, $product));
//$this->_setAttribute($id, 'is_imported', true);

        $this->_processUom($erpData, $product);
        $this->_processCustomOptions($erpData, $product);
        $this->_processErpImages($erpData, $product);
        $this->_processStock($erpData, $product);
        $this->_processAttributes($erpData, $product);
        $this->_processVisibility($erpData, $product, $currencies);
        $this->_processLocations($erpData, $product);
        $this->_processMetaInformation($erpData, $product);
        //M1 > M2 Translation Begin (Rule 12)
        //if (Mage::app()->useCache('message')) {
        if ($this->_cacheState->isEnabled('message')) {
            //$cache = Mage::app()->getCacheInstance();
            /* @var $cache Mage_Core_Model_Cache */
            //$cache->clean($product->getSku());
            $this->_cacheManager->clean($product->getSku());
            //M1 > M2 Translation End

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
    protected function _processConfigurable($product, $erpData)
    {
        $this->_validateConfiguration();
        $erpConfigurableData = $erpData->getParent();
        $configurableSKU = $erpConfigurableData->getProductCode();
        if ($configurableSKU) {
            $prodObj = $this->catalogProductFactory->create();
            $configurableProductId = $prodObj->getIdBySku($configurableSKU);
            $this->_processingConfigurable = true;
            $isnew = false;
            if (!$configurableProductId) {
                $isnew = true;
                $configProduct = $this->_createProduct($configurableSKU,
                    \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE, $this->_stkType);
            } else {
                $configProduct = $prodObj->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)->load($configurableProductId);
            }

            //associated product to configurable
            $configProduct = $this->_processConfigurableOption($product, $configProduct, $erpData);

            //set other extra attribute like location, image etc...
            $this->_processConfigurableData($product, $configProduct, $erpData, $isnew);
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
//                else {
//                    throw new \Exception(
//                        $this->getErrorDescription(self::STATUS_XML_TAG_MISSING, 'parent>productCode'), self::STATUS_XML_TAG_MISSING
//                    );
//                }
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
    protected function _processConfigurableOption($simpleProduct, $configProduct, $erpData)
    {
        $configProductId = $configProduct->getId();
        $simpleProductId = $simpleProduct->getId();
        $configProductSku = $configProduct->getSku();
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
        $configurableAttributesData = [];
        $eavConfig = $this->eavConfig;
        /* @var $eavConfig \Magento\Eav\Model\Config */
        foreach ($erpConfigurableAttrLists as $erpConfigurableAttrList) {
            $attribute = $eavConfig->getAttribute('catalog_product', $erpConfigurableAttrList);
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            if (!$attribute) {
                throw new \Exception(
                    "Product attribute {$erpConfigurableAttrList} not found to creat configurable product.",
                    self::STATUS_GENERAL_ERROR
                );
            }

            // Attribute Option
            $attrOptions = $attribute->getOptions();
            $attributeValues = [];
            if ($attrOptions) {
                foreach ($attrOptions as $attrOption) {
                    if ($attrOption->getValue()) {
                        $attributeValues[] = [
                            'label' => $attrOption->getLabel(),
                            'attribute_id' => $attribute->getId(),
                            'value_index' => $attrOption->getValue()
                        ];
                    }
                }
            }

            // Attribute
            if ($attribute) {
                $configurableAttributesData[] = [
                    'attribute_id' => $attribute->getId(),
                    'code' => $attribute->getAttributeCode(),
                    'label' => $attribute->getStoreLabel(),
                    'position' => $attribute->getPosition(),
                    'values' => $attributeValues
                ];
            }
        }
        $eavConfig->clear();

        $extensionConfigurableAttributes = $configProduct->getExtensionAttributes();
        /** @var $extensionConfigurableAttributes \Magento\Catalog\Api\Data\ProductExtension */

        //Validate link product
        if ($this->_exists()) {// no need to check when simple product creating
            //get all parent's
            $parentIds = $this->configurableType->getParentIdsByChild($simpleProductId);
            if ($parentIds && !in_array($configProduct->getId(), $parentIds)) {
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

        //Assign attribute's to configurable
        $configurableOptions = $this->optionFactory->create($configurableAttributesData);
        $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);

        //Assign products to configurable
        $configProductLink = $extensionConfigurableAttributes->getConfigurableProductLinks();
        if ($configProductLink && !array_key_exists($simpleProduct->getId(), $configProductLink)) {
            $existID = $extensionConfigurableAttributes->getConfigurableProductLinks();
            $existID[$simpleProduct->getId()] = $simpleProduct->getId();
            $extensionConfigurableAttributes->setConfigurableProductLinks($existID);
        } elseif (!$configProductLink) {
            $extensionConfigurableAttributes->setConfigurableProductLinks($simpleProduct->getId());
        }

        //Assign Attribute Extension to configurable
        $configProduct->setExtensionAttributes($extensionConfigurableAttributes);
        $configProduct->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
        $configProduct = $this->_productRepositoryInterface->save($configProduct);

        return $configProduct;
    }

    /**
     * @param \Epicor\Comm\Model\Product $product
     * @param \Epicor\Comm\Model\Product $configProduct
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param bool $isnew
     */
    protected function _processConfigurableData($product, $configProduct, $erpData, $isnew = false)
    {
        $id = $configProduct->getId();
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
                $this->_checkAndSetAttribute($id, 'ecc_related_documents',
                    $this->_getRelatedDocuments($parentErpData, $configProduct));
            } else {
                $this->_checkAndSetAttribute($id, 'ecc_related_documents',
                    $this->_getRelatedDocuments($erpData, $configProduct));
            }

            //Images
            $newImages = $this->_getGroupedData('images', 'image', $parentErpData);
            if (count($newImages) > 0) {
                $this->_processErpImages($parentErpData, $configProduct);
            } else {
                $this->_processErpImages($erpData, $configProduct);
            }

            //process for single UOM
            $this->_processUom($erpData, $configProduct);

            //Meta Information
            $this->_processMetaInformation($erpData, $configProduct);
            $this->_checkAndSetEccBrand($id, 'ecc_brand', $erpData->getSupplierBrand());
            $this->_checkAndSetEccBrandD($id, $erpData->getSupplierBrand());
        }

        $currencies = $this->_getCurrencies($erpData);
        $this->_processVisibility($erpData, $configProduct, $currencies);

        /**
         * We can't delete location from parent(configurable) product
         */
        $this->_processLocations($erpData, $configProduct);

        if ($this->_cacheState->isEnabled('message')) {
            $this->_cacheManager->clean($configProduct->getSku());
        }
    }

    /**
     * DATA GETTERS, gets values where values need working out.
     */

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
                $currencies[$code]['base_price'] = $price ?: "0";
                $currencies[$code]['cost_price'] = $cost ?: "0";
            }
        }

        //M1 > M2 Translation Begin (Rule p2-6.5)
        /*if (!array_key_exists(Mage::app()->getBaseCurrencyCode(), $currencies)) {
            $currencies[Mage::app()->getBaseCurrencyCode()]['base_price'] = 0;*/
        if (!array_key_exists($this->storeManager->getStore()->getBaseCurrencyCode(), $currencies)) {
            $currencies[$this->storeManager->getStore()->getBaseCurrencyCode()]['base_price'] = "0";
            //M1 > M2 Translation End
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
    protected function _getLeadTime($erpData, $product)
    {
        $leadTime = $product->getEccLeadTime();
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

    protected function _getRelatedDocuments($erpData, &$product)
    {
        $docSyncRequired = false;
        $documents = $this->_getGroupedData('related_documents', 'related_document', $erpData);
        $productRelatedDocuments = $product->getEccRelatedDocuments();

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
            $this->_checkAndSetAttribute($product->getId(), 'ecc_related_documents_synced', 0);
        }
        return $relatedDocuments;
    }

    /**
     * UPDATE FUNCTIONS - things that need updating that are too large or update multiple values
     */

    /**
     * Add EWA Code product custom option
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _processConfiguratorOption(&$product)
    {
// add custom option
        /* @var $product Mage_Catalog_Model_Product */

        $ewaCode = 0;
        $ewaTitle = 0;
        $ewaSku = 0;
        $ewaShortDesc = 0;
        $ewaDesc = 0;

// locate the configurator option on the product, so we don;t add it twice
        if ($product->getOptions()) {
            foreach ($product->getOptions() as $option) {

                /* @var $option Mage_Catalog_Model_Product_Option */
                if ($option->getType() == 'ewa_code') {
                    $ewaCode = $option->getId();
                } else {
                    if ($option->getType() == 'ewa_description') {
                        $ewaDesc = $option->getId();
                    } else {
                        if ($option->getType() == 'ewa_short_description') {
                            $ewaShortDesc = $option->getId();
                        } else {
                            if ($option->getType() == 'ewa_title') {
                                $ewaTitle = $option->getId();
                            } else {
                                if ($option->getType() == 'ewa_sku') {
                                    $ewaSku = $option->getId();
                                }
                            }
                        }
                    }
                }
            }
        }

        $optionInstance = $product->getOptionInstance();
        $customOptions = [];

        $ewaDescop = $this->customoptions->create();
        $ewaCodeop = $this->customoptions->create();
        $ewaShortDescop = $this->customoptions->create();
        $ewaTitleop = $this->customoptions->create();
        $ewaSkuop = $this->customoptions->create();

        if (empty($ewaDesc)) {
            $ewaDescop->setTitle('Ewa Description')
                ->setType('ewa_description')
                ->setIsRequire(0)
                ->setSortOrder(0)
                ->setProductSku($product->getSku());
            $customOptions[] = $ewaDescop;
        }

        if (empty($ewaCode)) {
            $ewaCodeop->setTitle('Ewa Code')
                ->setType('ewa_code')
                ->setIsRequire(0)
                ->setSortOrder(0)
                ->setProductSku($product->getSku());
            $customOptions[] = $ewaCodeop;
        }

        if (empty($ewaSku)) {

            $ewaSkuop->setTitle('Ewa SKU')
                ->setType('ewa_sku')
                ->setIsRequire(0)
                ->setSortOrder(0)
                ->setProductSku($product->getSku());
            $customOptions[] = $ewaSkuop;
        }

        if (empty($ewaShortDesc)) {
            $ewaShortDescop->setTitle('Ewa Short Description')
                ->setType('ewa_short_description')
                ->setIsRequire(0)
                ->setSortOrder(0)
                ->setProductSku($product->getSku());
            $customOptions[] = $ewaShortDescop;
        }

        if (empty($ewaTitle)) {

            $ewaTitleop->setTitle('Ewa Title')
                ->setType('ewa_title')
                ->setIsRequire(0)
                ->setSortOrder(0)
                ->setProductSku($product->getSku());
            $customOptions[] = $ewaTitleop;
        }
        if (!empty($customOptions)) {
            $product->setOptions($customOptions)->save();
        }

        $product->setHasOptions(1);
    }

    /**
     * Removes EWA options from product
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _removeConfiguratorOption(&$product)
    {
// add custom option
        /* @var $product Mage_Catalog_Model_Product */

        $codes = array(
            'ewa_code',
            'ewa_description',
            'ewa_short_description',
            'ewa_title',
            'ewa_sku',
        );

        $hasOptions = 0;

// locate the configurator option on the product, so we don;t add it twice
        foreach ($product->getOptions() as $option) {
            /* @var $option Mage_Catalog_Model_Product_Option */
            if (in_array($option->getType(), $codes)) {
                $option->delete();
            } else {
                $hasOptions = 1;
            }
        }

        if ($hasOptions != $product->getHasOptions()) {
            $this->_setStaticAttribute($product->getId(), 'has_options', $hasOptions);
        }
    }

    /**
     * Processes bundle configurable options
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     *
     * @throws \Exception
     */
    protected function _processBundleOptions($erpData, &$product)
    {
        $options = array();
        $optionSelections = array();
        $optionCount = 0;
        // Remove Existing Bundled Products
        $options_raw = $product->getTypeInstance()->getOptionsCollection($product)->getItems();
        foreach ($options_raw as $option) {
            $options[$optionCount] = array(
                'required' => $option->getRequired(),
                'option_id' => $option->getOptionId(),
                'parent_id' => $option->getParentId(),
                'position' => $option->getPosition(),
                'type' => $option->getType(),
                'title' => $option->getDefaultTitle(),
                'default_title' => $option->getDefaultTitle(),
                'delete' => 1,
            );
            $optionCount++;

        }


        $parts = $this->_getGroupedData('exploded_parts', 'exploded_part', $erpData);

        foreach ($parts as $explodedPart) {
            $productCode = $explodedPart->getProductCode();
            $productUom = $explodedPart->getUnitOfMeasureCode();
            $qty = $explodedPart->getQuantity();
            $description = $explodedPart->getDescription();
            $uomSeperator = $this->getHelper()->getUOMSeparator();
            if (!$description) {
                $description = $productCode . " " . $productUom;  // if no description in sku, load sku and uom only
            }
            $errorCode = false;
            $bundledProductId = $this->catalogProductFactory->create()->getIdBySku($productCode . $uomSeperator . $productUom);

            $uomBased = true;
            if (!$bundledProductId) {
                $bundledProductId = $this->catalogProductFactory->create()->getIdBySku($productCode);
                $uomBased = false;
            }

            if (!$bundledProductId) {
                $errorCode = self::STATUS_EXPLODED_PRODUCT_NOT_FOUND;
            } else {
                $bundledProduct = $this->catalogProductFactory->create()->load($bundledProductId);
                /* @var $bundledProduct Mage_Catalog_Model_Product */
                $allowProductTypes = array();
                //M1 > M2 Translation Begin (Rule 4)
                //$allowProductTypeNodes = Mage::getConfig()
                //       ->getNode('global/catalog/product/type/grouped/allow_product_types')->children();
                $allowProductTypeNodes = $this->globalConfig->get('catalog/product/type/grouped/allow_product_types');
                //M1 > M2 Translation End

                foreach ($allowProductTypeNodes as $type => $value) {
                    $allowProductTypes[] = $type;
                }

                if (!$bundledProduct->getId() || (!$uomBased && $bundledProduct->getEccDefaultUom() != $productUom)) {
                    $errorCode = self::STATUS_EXPLODED_PRODUCT_NOT_FOUND;
                } elseif (!in_array($bundledProduct->getTypeId(), $allowProductTypes)) {
                    $errorCode = self::STATUS_EXPLODED_PRODUCT_TYPE_NOT_ALLOWED;
                } else {
                    $bundledProductId = $bundledProduct->getId();
                }
            }

            if ($errorCode) {
                throw new \Exception(
                    $this->getErrorDescription($errorCode, $productCode, $productUom), self::STATUS_INVALID_PRODUCT_CODE
                );
            }

            $options[$optionCount] = array(
                'required' => 1,
                'option_id' => '',
                'position' => 0,
                'type' => 'select',
                'title' => $description,
                'default_title' => $description,
                'delete' => '',
            );

            $optionSelections[$optionCount][] = array(
                'product_id' => $bundledProductId,
                'selection_qty' => $qty,
                'selection_can_change_qty' => 0,
                'position' => 0,
                'is_default' => 1,
                'selection_id' => '',
                'selection_price_type' => 0,
                'selection_price_value' => 0.0,
                'option_id' => '',
                'delete' => ''
            );
            $optionCount++;
        }

        // Set the Bundle Options & Selection Data
        $product->setBundleOptionsData($options);
        $product->setBundleSelectionsData($optionSelections);

        if ($product->getBundleOptionsData()) {

            $productRepository = $this->catalogProductFactory->create();
            $options = [];
            foreach ($product->getBundleOptionsData() as $key => $optionData) {
                if (!(bool)$optionData['delete']) {
                    $option = $this->bundleOptionFactory->create(['data' => $optionData]);
                    $option->setSku($product->getSku());
                    $option->setOptionId(null);

                    $links = [];
                    $bundleLinks = $product->getBundleSelectionsData();
                    if (!empty($bundleLinks[$key])) {
                        foreach ($bundleLinks[$key] as $linkData) {
                            if (!(bool)$linkData['delete']) {
                                /** @var \Magento\Bundle\Api\Data\LinkInterface $link */
                                $link = $this->bundleLink->create(['data' => $linkData]);
                                $linkProduct = $productRepository->load($linkData['product_id']);
                                $link->setSku($linkProduct->getSku());
                                $link->setQty($linkData['selection_qty']);
                                if (isset($linkData['selection_can_change_qty'])) {
                                    $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
                                }
                                $links[] = $link;
                            }
                        }
                        $option->setProductLinks($links);
                        $options[] = $option;
                    }
                } else {
                    //delete options flagged for delete
                    $this->optionInterface->setData($optionData);
                    $this->bundleOptionRepository->delete($this->optionInterface);
                    $this->optionInterface->unsetData();
                }
                $extension = $product->getExtensionAttributes();
                $extension->setBundleProductOptions($options);
                $product->setExtensionAttributes($extension);
            }
            // need to do this or bundle options won't save
            $this->registry->unregister('product');
            $this->registry->register('product', $product);

            if ($this->_exists()) {
                //add foreach to process save options during update (products not being added to existing bundle otherwise)
                foreach ($options as $option) {
                    $this->productOptionRepository->save($product, $option);
                }
                $weightType = $this->getConfigFlag('kit_weight_fixed') ? 1 : 0;
                $this->_setAttribute($product->getId(), 'weight_type', $weightType);
                $this->_setAttribute($product->getId(), 'sku_type', 1);
                $this->_setAttribute($product->getId(), 'price_type', 1);
                $typeInstance = $product->getTypeInstance();
                /* @var $typeInstance Mage_Bundle_Model_Product_Type */
                $typeInstance->save($product);
            }
        }
    }

    /**
     * Processes uom related data
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function _processUom($erpData, &$product)
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
                $this->_checkAndSetAttribute($product->getId(), 'ecc_default_uom', $defaultCode);
            }

            if (!empty($defaultDesc)) {
                $packSize = $defaultDesc;
            }

            if (!empty($defaultDecimalPlaces)) {
                $decimalPlaces = $defaultDecimalPlaces;
            }
        } else {
            $this->_checkAndSetAttribute($product->getId(), 'ecc_default_uom', null);
        }

        $this->_checkAndSetAttribute($product->getId(), 'ecc_uom', $uom);
        $this->_checkAndSetAttribute($product->getId(), 'ecc_decimal_places', $decimalPlaces);
        $this->_checkAndSetAttribute($product->getId(), 'ecc_pack_size', $packSize);
        $this->setPackSizeConfig($product->getId(), $uom, $packSize);
    }

    public function setPackSizeConfig($productId, $uom = null, $packSize = null)
    {
        $stkPackSize = $this->scopeConfig->getValue('epicor_comm_field_mapping/stk_mapping/stk_display_pack_size_as',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        switch ($stkPackSize) {
            case "1":
                $this->_checkAndSetAttribute($productId, 'ecc_pack_size', $packSize);
                break;
            case "2":
                $this->_checkAndSetAttribute($productId, 'ecc_pack_size', $uom);
                break;
            case "3":
                $stkConcatenationText = $this->scopeConfig->getValue('epicor_comm_field_mapping/stk_mapping/stk_concatenation_text',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $this->_checkAndSetAttribute($productId, 'ecc_pack_size',
                    $uom . " " . $stkConcatenationText . " " . $packSize);
                break;
            default:
                $this->_checkAndSetAttribute($productId, 'ecc_pack_size', $packSize);

        }
    }

    /**
     * Processes ERP image data
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function _processErpImages($erpData, &$product)
    {
        if (!$this->isUpdateable('images_update', $this->_exists())) {
            return;
        }

        if (!$this->_processingUOMs) {
            $newImages = $this->_getGroupedData('images', 'image', $erpData);
            $this->_loadStores($erpData);

            $erpImages = $product->getEccErpImages();
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

            $this->_setAttribute($product->getId(), 'ecc_previous_erp_images', $product->getEccErpImages());
            $this->_setAttribute($product->getId(), 'ecc_erp_images', $erpImages);
            if (!empty($erpImages)) {
                $this->_setAttribute($product->getId(), 'ecc_erp_images_processed', 0);
            } else {
                $this->_setAttribute($product->getId(), 'ecc_erp_images_processed', 1);
            }
        } else {
            $this->_setAttribute($product->getId(), 'ecc_erp_images_processed', 1);
        }
    }

    /**
     * Processes optoins data
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function _processCustomOptions($erpData, &$product)
    {
        if (!$this->isUpdateable('custom_options_update', $this->_exists())) {
            return;
        }

        $newOptions = $this->_getGroupedData('options', 'option', $erpData);
        $optionInstance = $product->getOptionInstance();
        /* @var $optionInstance  \Magento\Catalog\Model\Product\Option */

        $ewaTypes = array(
            'ewa_code',
            'ewa_description',
            'ewa_short_description',
            'ewa_title',
            'ewa_sku',
        );

        $existingOptions = array();
        //M1 > M2 Translation Begin (Rule 4)
        if ($product->getOptions()) {
            foreach ($product->getOptions() as $oldOption) {                            // set existing options into array
                /* @var $oldOption Mage_Catalog_Model_Product_Option */
                if (!in_array($oldOption->getType(), $ewaTypes)) {
                    $existingOptions[$oldOption->getEccCode()] = $oldOption;
                }
            }
        }

        $existingCount = count($existingOptions);
        $hasErpOptions = false;

// locate the configurator option on the product, so we don;t add it twice
        $uploadedCodes = array();
        if (!empty($newOptions)) {

            $hasErpOptions = true;

            foreach ($newOptions as $newOption) {
                $code = $newOption->getCode();
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

                $isRequired = (strtoupper($newOption->getIsRequired()) == 'Y') ? true : false;

                $existingOption = isset($existingOptions[$code]) ? $existingOptions[$code] : false;
                $optionData = array();

                if ($existingOption) {
                    $optionData = $existingOption->getData();
                    if ($optionData['title'] != $newOption->getDescription()) {
                        $existingOption->delete();
                        $optionData = array();
                    }
                }

                $optionData['title'] = $newOption->getDescription();
                $optionData['type'] = $optionType;
                $optionData['is_require'] = $isRequired;
                $optionData['sort_order'] = $sortOrder;

// don't add options if the type is field, or hiddden
                if (!in_array($optionType, array('ecc_text_field', 'ecc_text_hidden'))) {
                    $optionData['values'] = $this->_getOptionValues($newOption);
                }

                $optionData['sku'] = '';
                $optionData['max_characters'] = $newOption->getLimit();
                $optionData['ecc_code'] = $newOption->getCode();
                $optionData['ecc_default_value'] = $newOption->getDefaultValue();
                $optionData['ecc_validation_code'] = $newOption->getValidationCode();

                $optionInstance->addOption($optionData);
                $existingCount++;
            }
        }

        foreach ($existingOptions as $option) {
            if ($option->getEccCode() && !in_array($option->getEccCode(),
                    $uploadedCodes) && !in_array($option->getType(), $ewaTypes)) {
                $option->delete();
                $existingCount--;
            }
        }

        if ($hasErpOptions || $existingCount > 1 || $this->_stkType == 'C') {
            $hasOptions = 1;
        } else {
            $hasOptions = 0;
        }

        if ($hasOptions != $product->getHasOptions()) {
            $this->_setStaticAttribute($product->getId(), 'has_options', $hasOptions);
        }

        if ($existingCount > 0) {
            $productId = $product->getId();
            $storeId = $product->getStoreId();
            $_options = $optionInstance->getOptions();
            $this->_setStaticAttribute($product->getId(), 'can_save_custom_options', true);
            foreach ($_options as $_option) {
                $customOption = $this->customoptions->create();
                $customOption->setProductId($productId)
                    ->setStoreId($storeId)
                    ->addData($_option);
                $customOption->save();
                $product->addOption($customOption);
            }
        }

        $product->getOptionInstance()->unsetOptions();
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
                    'title' => $value->getTitle(),
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
    protected function _processStock($erpData, $product)
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
            $stockDataArray['manage_stock'] = $product->getTypeId() == 'grouped' ? 1 : 0;
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
            $stockItem = $this->catalogInventoryStockItemFactory->create();
            /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
            //M1 > M2 Translation Begin (Rule 6)
            //$stockItem->loadByProduct($product->getId());
            $stockItem->getResource()->loadByProductId($stockItem, $product->getId(), $stockItem->getStockId());
            //M1 > M2 Translation End

            $stockItemId = $stockItem->getId();

            if (!$stockItemId) {
                $stockItem->setData('stock_id', 1);
            }

            $stockItem->setData('product_id', $product->getId());
            $stockItem->setProduct($product);

            foreach ($stockDataArray as $field => $value) {
                $stockItem->setData($field, $value ? $value : 0);
            }
            $stockItem->save();

            $this->_indexProducts[] = $product->getId();
        }
    }

    /**
     * Process Attributes Repeating Group
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Product $product
     *
     */
    protected function _processAttributes($erpData, &$product)
    {
        if (!$this->isUpdateable('product_attributes_update', $this->_exists())) {
            return;
        }
        $eavConfig = $this->eavConfig;
        /* @var $eavConfig \Magento\Eav\Model\Config */

        $attributes = $this->_getGroupedData('attributes', 'attribute', $erpData);
        $indexProductAttributes = array();

        $processMissingAttributes = $this->isUpdateable('ecc_process_missing_attributes', $this->_exists());
        $allSTKAttr = [];
        if ($processMissingAttributes) {
            $allSTKAttr = $this->_getAllStkAttributes($this->_attributeSet);
        }
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
            $attributeTable = $this->commErpMappingAttributesFactory->create()->load($code, 'attribute_code');
            $this->_erpMappingAttributes[$code] = !$attributeTable->isObjectNew() ? $attributeTable->getData() : null;
            $attributeModel = $eavConfig->getAttribute('catalog_product', $code);
            if ($attributeModel instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute && !$attributeModel->isObjectNew()) {

                if ($this->isUpdateable('product_attributes_description_update',
                        !$attributeModel->isObjectNew()) && $description) {   // label
                    // if already exists, update description by frontend label
                    //perforamce imrpovement END
                    if ($description != $attributeModel->getFrontendLabel()
                        && $attributeModel->getEccCreatedBy() != 'N') {
                        //  $attributeModel->setFrontendLabel($description)->save();  //done here otherwise only saved for new attributes
                        $where = ['attribute_id=?' => $attributeModel->getId()];
                        $data = ['frontend_label' => $description];
                        $this->updateData(
                            $attributeModel->getResource()->getMainTable(),
                            $data,
                            $where
                        );
                        $attributeModel->setFrontendLabel($description);
                    }
                    //perforamce imrpovement END
                }
                if ($this->isUpdateable('product_attributes_value_update', $this->_exists())) {
                    $value = $attribute->getValue();
                    $this->_saveAttributeValues($attributeModel, $code, $value);
                    $this->_setAttribute($product->getId(), $code, $value);
                    if ($this->_oldAttributeSet != $attributeSetId) {
                        $this->attributeManagement->assign(
                            'catalog_product',
                            $attributeSetId,
                            $attributeGroupId,
                            $code,
                            $this->_getAttributeSortOrder($attributeGroupId, false, $attributeModel->getId(),
                                $attributeGroupId)
                        );
                    }
                }
            } else {
                $this->_createAttribute($product, $code, $description, $attribute);
                $eavConfig = $this->eavConfigFactory->create();
                /* @var $eavConfig \Magento\Eav\Model\Config */
                $attributeModel = $eavConfig->getAttribute('catalog_product', $code);
                $product->getResource()->addAttribute($attributeModel);

                // convert value to 1 or 0 if boolean
                if ($attributeModel->getFrontendInput() == 'boolean') {
                    $attribute->setValue($this->isTrue($attribute->getValue()) ? 1 : 0);
                }
                $product->addAttributeUpdate($code, $attribute->getValue(), $product->getStoreId());


                //set attribute options
                $this->_saveAttributeValues($attributeModel, $code, $attribute->getValue(), $product);
                //set attribute value
                $this->_setAttribute($product->getId(), $code, $attribute->getValue());
                $this->attributeManagement->assign(
                    'catalog_product',
                    $attributeSetId,
                    $attributeGroupId,
                    $code,
                    $this->_getAttributeSortOrder($attributeGroupId, true, $attributeModel->getId())
                );
            }
            $indexProductAttributes[$attributeModel->getAttributeId()] = $attributeModel->getAttributeCode();
        }
        foreach ($allSTKAttr as $stkAttr) {
            $code = strtolower($stkAttr);
            //Determine if attribute is on epicor_comm_erp_attributes. if so use settings retrieved
            //we can't use $this->commErpMappingAttributesFactory() will have give old object
            $attributeTable = $this->commErpMappingAttributesFactory->create()->load($code, 'attribute_code');
            $this->_erpMappingAttributes[$code] = !$attributeTable->isObjectNew() ? $attributeTable->getData() : null;
            $attributeModel = $eavConfig->getAttribute('catalog_product', $code);
            if ($attributeModel instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute &&
                !$attributeModel->isObjectNew()) {
                if ($this->isUpdateable('product_attributes_value_update', $this->_exists())
                    || $processMissingAttributes) {
                    $value = '';
                    $this->_saveAttributeValues($attributeModel, $code, $value);
                    $this->_setAttribute($product->getId(), $code, $value);
                }

            }
        }
        $this->eavConfig->clear();
        if (!empty($indexProductAttributes)) {
            $this->registry->unregister('index_product_attributes');
            $this->registry->register('index_product_attributes', $indexProductAttributes);
        }
    }

    protected function updateData($tableName, $data, $where)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->update($tableName, $data, $where);
    }

    protected function commErpMappingAttributesFactory()
    {
        if (!$this->_commErpMappingAttributesFactory) {
            $this->_commErpMappingAttributesFactory = $this->commErpMappingAttributesFactory->create();
        }
        return $this->_commErpMappingAttributesFactory;
    }

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
        $this->_indexProducts[] = $product->getId();
        return $newAttribute;
    }

    /**
     * Determines which stores the product is enabled / visible on
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     * @param array $currencies
     */
    protected function _processVisibility($erpData, $product, $currencies)
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
        //M1 > M2 Translation End

        $visible = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH;
        $notVisible = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE;

        $enabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $disabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;

        $websites = $product->getWebsiteIds();
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
                $this->_setAttribute($product->getId(), 'price', $productPrice, $store->getId());
                $this->_setAttribute($product->getId(), 'cost', $productCost, $store->getId());
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
                $this->_setAttribute($product->getId(), 'visibility', $productVisibility, $store->getId());
                $this->updateVisibilityIndex($product->getId(), $store->getId(), $productVisibility, $this->_exists());
            }

            //no update for configurable
            if ($visibilityUpdate && $productStatus !== false) {
                $this->_setAttribute($product->getId(), 'status', $productStatus, $store->getId());
                if ($productStatus == $enabled && !in_array($websiteId, $websites)) {
                    $websites[] = $websiteId;
                }
            }
        }

        //Pricing SKU and config price
        if ($productPrice !== false && $this->_processingConfigurable) {
            $pricingSku = null;
            $parentErpData = $erpData->getParent();
            $configLowerPrice = $product->getData("ecc_configurable_part_price");
            if (!$configLowerPrice) {
                $configLowerPrice = 0;
            }

            //pricing sku and set lowest price or associated product
            if ($configLowestProductPrice < $configLowerPrice || $configLowerPrice === 0) {
                $this->_setAttribute($product->getId(), 'ecc_configurable_part_price', $configLowestProductPrice,
                    $store->getId());
                $pricingSku = $erpData->getProductCode();
            }

            if ($parentErpData->getPricingProductCode()) { // available with XMl
                $pricingProductCode = $parentErpData->getPricingProductCode();
                $prodObj = $this->catalogProductFactory->create();
                $validateProduct = $prodObj->getIdBySku($pricingProductCode);
                if ($validateProduct) {
                    $pricingSku = $pricingProductCode;
                }
            }

            if ($pricingSku) {
                $this->_setAttribute($product->getId(), 'ecc_pricing_sku', $pricingSku);
            }
        }

        /**
         * Update/Add Pricing SKU with lowest Price
         * only for configurable product
         */
//        if ($this->_processingConfigurable && $this->_isConfigurable && $product->getTypeId() == "configurable") {
//            $parentErpData = $erpData->getParent();
//
//            //Pricing Sku with XML
//            if ($parentErpData->getPricingProductCode()) { // available with XMl
//                $this->_setAttribute($product->getId(), 'ecc_pricing_sku', $parentErpData->getPricingProductCode());
//            } else {//Calculate lowest price
//                $data = $product->getTypeInstance()->getConfigurableOptions($product);
//                $options = array();
//                foreach ($data as $attr) {
//                    foreach ($attr as $p) {
//                        $options[$p['sku']][$p['attribute_code']] = $p['option_title'];
//                    }
//                }
//
//                if(count($options) > 1) {//More than one associated product
//                    foreach ($options as $sku => $attributeOptions) {
//                        if($lowestPriceData["pricingSku"] == $sku){ //Skip to load if same simple product is there
//                            continue;
//                        }
//                        $childProduct = $this->_productRepositoryInterface->get($sku);
//                        if ($childProduct) {
//                            $price = $childProduct->getPrice();
//                            if ($price && $price < $lowestPriceData["price"]) {
//                                $lowestPriceData["price"] = $price;
//                                $lowestPriceData["pricingSku"] = $childProduct->getSku();
//                            }
//                        }
//                    }
//                }
//                $this->_setAttribute($product->getId(), 'ecc_pricing_sku', $lowestPriceData["pricingSku"]);
//            }
//        }

        /* set not visible for the store which is not matched */
        $productStoreIds = $this->_loadStoresFromCompanyBranding();
        $visibleStoresIds = array_keys($visibleStores);
        $resetStoreId = array_merge(array_diff($productStoreIds, $visibleStoresIds),
            array_diff($visibleStoresIds, $productStoreIds));
        if ($productStoreIds != array_diff($productStoreIds, $visibleStoresIds)) {
            foreach ($resetStoreId as $store) {
                if ($visibilityUpdate) {
                    $this->_setAttribute($product->getId(), 'visibility', $notVisible, $store);
                    /* product status is handled in website scope cannot handle in store view level */
                    //$this->_setAttribute($product->getId(), 'status', $disabled, $store);
                }
            }
        }

        $diff = array_merge(array_diff($visibileWebsiteIds, $product->getWebsiteIds()),
            array_diff($product->getWebsiteIds(), $visibileWebsiteIds));
        if (!empty($diff)) {
            $this->_setStaticAttribute($product->getId(), 'website_ids', $websites);
        }
    }

    /**
     * Updates Product Locations
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _processLocations($erpData, $product)
    {
        if (!$this->isUpdateable('locations_update', $this->_exists())) {
            return;
        }

        $helper = $this->commLocationsHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Locations */

        $company = $this->getCompany();
        $stores = $this->_loadStores();
        $locations = $this->_getGroupedData('locations', 'location', $erpData);
        $currentLocations = $product->getLocationsWithoutExtra();

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

        $newLocations = array();
        foreach ($locations as $location) {
            $country = $location->getCountry();
            $_location = $helper->checkAndCreateLocation($location->getLocationCode(), $company, $stores, $country);
            $_locationCode = is_null($_location) ? $location->getLocationCode() : $_location->getCode();
            $_locationData = $this->_getLocationData($location);
            if (isset($_locationData['location_code'])) {
                $_locationData['location_code'] = $_locationCode;
            }
            $locationModel = $product->setLocationData($_locationCode, $_locationData, $this->_storesDefaultCurrency,
                $updateFlags);
            $locationModel->save();
            $newLocations[] = $_locationCode;
        }

        if (is_array($currentLocations)) {
            foreach ($currentLocations as $location) {
                /* @var $location \Epicor\Comm\Model\Location\Product */
                // Don't remove any location from Configurable product
                if (!in_array($location->getLocationCode(), $newLocations)
                    && !$this->_processingConfigurable
                    && $company == $location->getCompany()
                ) {
                    $location->delete();
                }
            }
        }
    }

    /**
     * Updates Meta Information
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _processMetaInformation($erpData, $product)
    {
        if (!$this->isUpdateable('metainformation_update', $this->_exists())) {
            return;
        }

        $productId = $product->getId();

        $metaInformation = $erpData->getMetaInformation();
        if ($metaInformation instanceof \Epicor\Common\Model\Xmlvarien) {
            $data = $metaInformation->getData();

            if (isset($data['title'])) {
                $this->_setAttribute($productId, 'meta_title', $data['title']);
            }
            if (isset($data['keywords'])) {
                $this->_setAttribute($productId, 'meta_keyword', $data['keywords']);
            }
            if (isset($data['description'])) {
                $this->_setAttribute($productId, 'meta_description', $data['description']);
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
     * UTILITY
     */

    /**
     * Changes a products type and removes any data that needs to be done for the change
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $newType
     */
    protected function _changeProductType(&$product, $newType)
    {
        $oldType = $product->getTypeId();

        if ($oldType == 'grouped') {
// delete all associated products of the grouped product
            //M1 > M2 Translation Begin (Rule 42)
            // $originalLinkedProducts = $product->getGroupedLinkCollection()->getItems();
            $originalLinkedProducts = array_values($product->getTypeInstance()->getChildrenIds($product->getId()));
            $originalLinkedProducts = $originalLinkedProducts[0];
            //M1 > M2 Translation End
            foreach ($originalLinkedProducts as $originalLinkedProduct) {
                $deleteProduct = $this->_productRepositoryInterface->getById($originalLinkedProduct);
                $deleteProduct->delete();
                $this->_processCheckpoint('Deleted Old UOM Product');
            }
        } else {
            if ($oldType == 'bundle') {
// delete all bundle option of the product
                $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product), $product);

                foreach ($selectionCollection as $option) {
                    $optionModel = $this->bundleOptionFactory->create();
                    $optionModel->setId($option->getOptionId());
                    $optionModel->delete();
                }
            }
        }

// have to do a save here, otherwise typeinstance code won't work (e.g. changing from simple to bundle)

        $product->setTypeId($newType);
        $product->save();
        $this->_indexProducts[] = $product->getId();
    }

    /**
     * Updates product attributes
     *
     * Main DB interaciton goes on in here
     */
    protected function _updateAttributes()
    {
        /* @var $action \Magento\Catalog\Model\ResourceModel\Product\Action */
        $action = $this->catalogResourceModelProductActionFactory->create();

        foreach ($this->_attributesToUpdate as $storeId => $product) {
            foreach ($product as $productId => $attData) {

                $product = $this->catalogProductFactory->create()->setStoreId($storeId)->load($productId);
                $changes = array();

                foreach ($attData as $code => $value) {
                    $data = $product->getData($code);
                    if (in_array($code, $this->_serialize)) {
                        $data = serialize($data);
                    }
                    if (array_key_exists($code, $this->_selectValues)) {
                        // process select and multiselect values
                        $this->_processSelectAttribute($product, $code, $value);
                    } else {
                        if (isset($this->_erpMappingAttributes[$code]['input_type']) && $this->_erpMappingAttributes[$code]['input_type'] == 'boolean') {
                            $value = $this->isTrue($value) ? 1 : 0;
                        }
                        //don't add to changes if new attribute - the value has already been set
                        // below if condition is commented to fix the issue of updating a value for child product of group product
                        // when new group product with new attribute is created

                        if ($data != $value) {
                            $changes[$code] = $value;
                        }
                    }
                }

                if (!empty($changes)) {
                    $this->_indexProducts[] = $productId;
                    $action->updateAttributes(array($productId), $changes, $storeId);
                }
            }
        }

        if (!empty($this->_staticAttributesToUpdate)) {
            foreach ($this->_staticAttributesToUpdate as $productId => $attData) {
                $product = $this->catalogProductFactory->create()->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)->load($productId);
                $changes = false;
                foreach ($attData as $key => $value) {
                    $getKey = 'get' . ucfirst($this->getHelper()->convertStringToCamelCase($key));
                    $setKey = 'set' . ucfirst($this->getHelper()->convertStringToCamelCase($key));
                    if ($product->$getKey() !== $value) {
                        $changes = true;
                        $product->$setKey($value);
                    }
                }

                if ($changes) {
                    $product->save();
                    $this->_indexProducts[] = $product->getId();
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
     * Checks if ECC Brand Dropdown is updateable and updates it if so
     *
     * @param integer $productId
     * @param mixed $value
     * @param integer $storeId
     */
    protected function _checkAndSetEccBrandD($productId, $value, $storeId = null)
    {
        $code = 'ecc_brand';
        $eccbrandCode = 'ecc_brand_updated';
        $flagBase = isset($this->_updateConfMap[$code]) ? $this->_updateConfMap[$code] : $code;

        if ($this->isUpdateable($flagBase . '_update', $this->_exists())) {
            if ($value == '' || $value == null) {
                $result = null;
            } else {
                $result = $this->attributeOptions->getOptionLabelValue($eccbrandCode, $value);
                if (empty($result)) {
                    $id = $this->attributeOptions->addAttributeOption($eccbrandCode, $value);
                    $oId = explode('_', $id);
                    $result = $oId[1];
                }
            }
            $this->_setAttribute($productId, $eccbrandCode, $result, $storeId);
        }
    }

    /**
     * Sets a static attribute for update
     *
     * @param integer $productId
     * @param string $code
     * @param mixed $value
     */
    protected function _setStaticAttribute($productId, $code, $value)
    {
        if (!isset($this->_staticAttributesToUpdate[$productId])) {
            $this->_staticAttributesToUpdate[$productId] = array();
        }

        $this->_staticAttributesToUpdate[$productId][$code] = $value;
    }

    /**
     * Sets attributes for saving
     *
     * @param integer $productId
     * @param string $code
     * @param mixed $value
     * @param integer $storeId
     */
    protected function _setAttribute($productId, $code, $value, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        if (!isset($this->_attributesToUpdate[$storeId])) {
            $this->_attributesToUpdate[$storeId] = array();
        }

        if (!isset($this->_attributesToUpdate[$storeId][$productId])) {
            $this->_attributesToUpdate[$storeId][$productId] = array();
        }

        if (in_array($code, $this->_serialize)) {
            $value = serialize($value);
        }

        $this->_attributesToUpdate[$storeId][$productId][$code] = $value;
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
        parent::beforeProcessAction();
        //  $this->_disableIndexing();
    }

    public function afterProcessAction()
    {
        //  $this->_resetIndexing();
        $this->_index();
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
    protected function _processSelectAttribute($product, $code, $values)
    {
        $codeValues = $this->_selectValues[$code];
        $attributeOptions = $this->eavEntityAttributeSourceTableFactory->create();
        $returnValue = false;
        $separator = (isset($this->_erpMappingAttributes[$code]['separator'])) ? $this->_erpMappingAttributes[$code]['separator'] : null;

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
            foreach ($valuesArray as $value) {
                //don't add empty value as option
                if (!$value) {
                    $product->unsetData($code)->getResource()->saveAttribute($product, $code);
                    continue;
                }
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
                } else {
                    //if dropdown, set attribute to new attribute value
                    if ($attribute->getFrontendInput() == 'select') {
                        $getKey = 'get' . ucfirst($this->getHelper()->convertStringToCamelCase($code));
                        if ($product->$getKey() != $optionKeys[$value]) {
                            $returnValue = true;
                        }
                    }
                }
                // set the value of the attribute to the supplied value
                if ($returnValue) {
                    $attributeResource = $this->catalogResourceModelEavAttributeFactory->create()->load($codeValue['id']);
                    if ($attributeResource->usesSource()) {  // condition shouldn't be needed as option is dropdown, but will prevent error if it happens
                        $optionId = $attributeResource->getSource()->getOptionId($value);
                        $product->setData($code, $optionId)->getResource()->saveAttribute($product, $code);
                    }
                }
            }
        }
        if ($attribute->getFrontendInput() == 'multiselect') {
            $attributeResource = $this->catalogResourceModelEavAttributeFactory->create()->load($codeValue['id']);
            if ($attributeResource->usesSource()) {  // condition shouldn't be needed as option is multiselect, but will prevent error if needed
                $optionId = array();
                foreach ($valuesArray as $value) {
                    $optionId[] = $attributeResource->getSource()->getOptionId($value);
                    //          $oldOptions[$value] = $attributeResource->getSource()->getOptionId($value);
                }

                //remove unused attribute options if required, NB these have to be done separately
//                $oldOptionsNotRequired = array_diff_key($optionKeys, $oldOptions);
//                $removeOptions = array();
//                foreach ($oldOptionsNotRequired as $key => $oldOption) {
//                    $removeOption['delete'][$oldOption] = true;
//                    $removeOption['value'][$oldOption] = true;
//                    $setup->addAttributeOption($removeOption);
//                }
                //set all options to active for product
                $product->setData($code, implode(",", $optionId))->getResource()->saveAttribute($product, $code);
            }
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
        if (in_array($attributeModel->getFrontendInput(), $requiredArray)) {
            $this->_selectValues[$code][] = array('id' => $attributeModel->getAttributeId(), 'value' => $value);
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
                'input' => $attributeTable['input_type'],
                'is_visible_in_advanced_search' => $attributeTable['is_visible_in_advanced_search'],
                'is_searchable' => $attributeTable['is_searchable'],
                'is_comparable' => $attributeTable['is_comparable'],
                'is_filterable' => $attributeTable['is_filterable'],
                'is_filterable_in_search' => $attributeTable['is_filterable_in_search'],
                'is_used_for_promo_rules' => $attributeTable['is_used_for_promo_rules'],
                'is_html_allowed_on_front' => $attributeTable['is_html_allowed_on_front'],
                'is_visible_on_front' => $attributeTable['is_visible_on_front'],
                'used_in_product_listing' => $attributeTable['used_in_product_listing'],
                'used_for_sort_by' => $attributeTable['used_for_sort_by']
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
            'group' => '',
            'label' => $description,
            'type' => 'varchar',
            'input' => 'text',
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => true,
            'is_visible_on_front' => (is_array($attributeTable)) ? $attributeTable['is_visible_on_front']
                                    : $this->scopeConfig->getValue(self::XML_PATH_STK_NAVF,
                                                            ScopeInterface::SCOPE_STORE),
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

    public function getAttrGroupId($attrId, $setId)
    {
        if (!$this->_attributegroup) {
            $productHelper = $this->commProductHelper();
            $this->_attributegroup = $productHelper->getAttributeGroupId($attrId, $setId);
        }
        $getAttributeGroupIdValue = isset($this->_attributegroup[$attrId . 'setId' . $setId]) ? $this->_attributegroup[$attrId . 'setId' . $setId] : null;
        return $getAttributeGroupIdValue;
    }

    public function getAttributeId($attributeCode)
    {
        if (!$this->_attributeId) {
            $productHelper = $this->commProductHelper();
            $this->_attributeId = $productHelper->getAllProductAttribute();
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
        $productLevel = ((isset($this->_defaultUom['decimal_places']) && !empty($this->_defaultUom['decimal_places'])) && $this->_defaultUom['decimal_places'] > 0) ? 1 : 0;
        if ($productLevel) {
            $acceptDecimal = true;
        } elseif (!$productLevel && $globalLevel) {
            $acceptDecimal = true;
        }
        return $acceptDecimal;
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

}
