<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Import;

use Epicor\Comm\Helper\Messaging;
use Epicor\Comm\Service\AttributeOptions;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogImportExport\Model\Import\Product\ImageTypeProcessor;
use Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\StatusProcessor;
use Magento\CatalogImportExport\Model\Import\Product\StockProcessor;
use Magento\CatalogImportExport\Model\StockItemImporterInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Import entity product model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Product extends \Magento\CatalogImportExport\Model\Import\Product
{


    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    protected $_deleteProducts = [];

    /**
     * @var \Magento\ImportExport\Model\Import\Config
     */
    protected $_importConfig;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory
     */
    protected $_resourceFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModel
     */
    protected $_resource;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Type\Factory
     */
    protected $_productTypeFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory
     */
    protected $_proxyProdFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Product\StoreResolver
     */
    protected $storeResolver;

    /**
     * @var Product\SkuProcessor
     */
    protected $skuProcessor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 100.0.3
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    protected $websitesCache = [];

    /**
     * @var array
     */
    protected $categoriesCache = [];

    /**
     * @var array
     * @since 100.0.3
     */
    protected $productUrlSuffix = [];

    /**
     * @var array
     * @deprecated 100.0.3
     * @since 100.0.3
     */
    protected $productUrlKeys = [];

    /**
     * Instance of product tax class processor.
     *
     * @var Product\TaxClassProcessor
     */
    protected $taxClassProcessor;

    /**
     * @var Product\Validator
     */
    protected $validator;

    /**
     * Array of validated rows.
     *
     * @var array
     */
    protected $validatedRows;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * {@inheritdoc}
     */
    protected $masterAttributeCode = 'sku';

    /**
     * @var ObjectRelationProcessor
     */
    protected $objectRelationProcessor;

    /**
     * @var TransactionManagerInterface
     */
    protected $transactionManager;

    /**
     * Flag for replace operation.
     *
     * @var null
     */
    protected $_replaceFlag = null;

    /**
     * Flag for replace operation.
     *
     * @var null
     */
    protected $cachedImages = null;

    /**
     * @var array
     * @since 100.0.3
     */
    protected $urlKeys = [];

    /**
     * @var array
     * @since 100.0.3
     */
    protected $rowNumbers = [];

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * Catalog config.
     *
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var ImageTypeProcessor
     */
    private $imageTypeProcessor;

    /**
     * Provide ability to process and save images during import.
     *
     * @var MediaGalleryProcessor
     */
    private $mediaProcessor;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\ActionFactory
     */
    protected $catalogResourceModelProductActionFactory;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * Stock Item Importer
     *
     * @var StockItemImporterInterface
     */
    private $stockItemImporter;

    /**
     * @var StatusProcessor
     */
    private $statusProcessor;
    /**
     * @var StockProcessor
     */
    private $stockProcessor;

    /**
     * @var \Magento\Indexer\Model\Indexer\State
     */
    protected $indexerState;
    /**
     * @var AttributeOptions|null
     */
    private $attributeOptions;

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\ImportExport\Model\Import\Config $importConfig
     * @param Proxy\Product\ResourceModelFactory $resourceFactory
     * @param Product\OptionFactory $optionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param Product\Type\Factory $productTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory
     * @param Proxy\ProductFactory $proxyProdFactory
     * @param UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac
     * @param DateTime\TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param Product\StoreResolver $storeResolver
     * @param Product\SkuProcessor $skuProcessor
     * @param Product\CategoryProcessor $categoryProcessor
     * @param Product\Validator $validator
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @param TransactionManagerInterface $transactionManager
     * @param Product\TaxClassProcessor $taxClassProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param array $data
     * @param array $dateAttrCodes
     * @param CatalogConfig $catalogConfig
     * @param ImageTypeProcessor $imageTypeProcessor
     * @param MediaGalleryProcessor $mediaProcessor
     * @param StockItemImporterInterface|null $stockItemImporter
     * @param DateTimeFactory $dateTimeFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ImportExport\Model\Import\Config $importConfig,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory,
        \Epicor\Comm\Model\Import\OptionFactory $optionFactory2,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory,
        \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        DateTime $dateTime,
        \Psr\Log\LoggerInterface $corelogger,
        \Epicor\Comm\Model\Import\Logger $logger,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\Validator $validator,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        \Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor $taxClassProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Catalog\Model\ResourceModel\Product\ActionFactory $catalogResourceModelProductActionFactory,
        UrlPersistInterface $urlPersist,
        \Magento\Indexer\Model\Indexer\State $indexerState,
        Messaging $commMessagingHelper,
        array $data = [],
        array $dateAttrCodes = [],
        CatalogConfig $catalogConfig = null,
        ImageTypeProcessor $imageTypeProcessor = null,
        MediaGalleryProcessor $mediaProcessor = null,
        StockItemImporterInterface $stockItemImporter = null,
        DateTimeFactory $dateTimeFactory = null,
        ProductRepositoryInterface $productRepository = null,
        StatusProcessor $statusProcessor = null,
        StockProcessor $stockProcessor = null,
        AttributeOptions $attributeOptions = null
    ) {
        $this->_catalogData = $catalogData;
        $this->_importConfig = $importConfig;
        $this->_resourceFactory = $resourceFactory;
        $this->_productTypeFactory = $productTypeFactory;
        $this->_proxyProdFactory = $proxyProdFactory;
        $this->_localeDate = $localeDate;
        $this->dateTime = $dateTime;
        $this->_logger = $logger;
        $this->storeResolver = $storeResolver;
        $this->skuProcessor = $skuProcessor;
        $this->validator = $validator;
        $this->objectRelationProcessor = $objectRelationProcessor;
        $this->transactionManager = $transactionManager;
        $this->taxClassProcessor = $taxClassProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->dateAttrCodes = array_merge($this->dateAttrCodes, $dateAttrCodes);
        $this->catalogConfig = $catalogConfig ?: ObjectManager::getInstance()->get(CatalogConfig::class);
        $this->imageTypeProcessor = $imageTypeProcessor ?: ObjectManager::getInstance()->get(ImageTypeProcessor::class);
        $this->mediaProcessor = $mediaProcessor ?: ObjectManager::getInstance()->get(MediaGalleryProcessor::class);
        $this->stockItemImporter = $stockItemImporter ?: ObjectManager::getInstance()
            ->get(StockItemImporterInterface::class);
        $this->statusProcessor = $statusProcessor ?: ObjectManager::getInstance()
            ->get(StatusProcessor::class);
        $this->stockProcessor = $stockProcessor ?: ObjectManager::getInstance()
            ->get(StockProcessor::class);
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator,
            $eventManager,
            $stockRegistry,
            $stockConfiguration,
            $stockStateProvider,
            $catalogData,
            $importConfig,
            $resourceFactory,
            $optionFactory,
            $setColFactory,
            $productTypeFactory,
            $linkFactory,
            $proxyProdFactory,
            $uploaderFactory,
            $filesystem,
            $stockResItemFac,
            $localeDate,
            $dateTime,
            $corelogger,
            $indexerRegistry,
            $storeResolver,
            $skuProcessor,
            $categoryProcessor,
            $validator,
            $objectRelationProcessor,
            $transactionManager,
            $taxClassProcessor,
            $scopeConfig,
            $productUrl,
            [],
            [],
            $catalogConfig,
            $imageTypeProcessor,
            $mediaProcessor,
            $stockItemImporter,
            $dateTimeFactory,
            $productRepository,
            $statusProcessor,
            $stockProcessor
        );

        $this->productRepository = $productRepository ?? ObjectManager::getInstance()
                ->get(ProductRepositoryInterface::class);
        $this->dateTimeFactory = $dateTimeFactory ?? ObjectManager::getInstance()->get(DateTimeFactory::class);
        $this->_optionEntity = $optionFactory2->create(['data' => ['product_entity' => $this]]);
        $this->catalogResourceModelProductActionFactory = $catalogResourceModelProductActionFactory;
        $this->urlPersist = $urlPersist;
        $this->indexerState = $indexerState;
        $this->attributeOptions = $attributeOptions ?: ObjectManager::getInstance()->get(AttributeOptions::class);
    }

    /**
     * Save products data.
     *
     * @param array $_productDataArray Product data.
     * @param array $websitesCache Website data.
     * @param array $_processNewattributeOption New Attribute Option Data data.
     * @param array $_newAttribute New Attribute data.
     * @return $this
     */
    public function saveProductsData($_productDataArray, $websitesCache = [], $_processNewattributeOption = false, $_newAttribute = [])
    {
        if (isset($_productDataArray['delete_products'])) {
            $this->_deleteProducts = $_productDataArray['delete_products'];
            unset($_productDataArray['delete_products']);
            $this->_deleteProducts();
        }

        if (isset($_newAttribute['product_typ_changed'])) {
            unset($_newAttribute['product_typ_changed']);
            $this->_oldSku = $this->skuProcessor->reloadOldSkus()->getOldSkus();
        }
        if (isset($_newAttribute['update_sku'])) {
            unset($_newAttribute['update_sku']);
            $this->_oldSku = $this->skuProcessor->reloadOldSkus()->getOldSkus();
        }
        $lastId = $this->saveProducts(
            $websitesCache,
            $_productDataArray,
            $_processNewattributeOption,
            $_newAttribute
        );
        //Save Stock
        $this->_saveStockItem();

        //Save Custom Options.
        $this->saveCustomOptions(
            $_productDataArray
        );

        //Save Location Data.
        $this->saveLocationsData(
            $_productDataArray
        );

        //Process All Type of products
        foreach ($this->_productTypeModels as $productTypeModel) {
            $productTypeModel->saveData();
        }
        //Save manufacturers Data issue is because of ecc_manufacturers is multi drop type.
        $this->saveManufacturersData(
            $_productDataArray
        );
        //Save Price and tax class id.
        $this->saveParentProductData(
            $_productDataArray
        );
        //Invalidate Index.
        $this->invalidateIndex();

        //Delete Last import Data Entry
        $importTable = $this->_connection->getTableName('importexport_importdata');
        $this->_connection->delete($importTable, ['id <= ?' => $lastId]);

        return $this;
    }

    /**
     * Parse values of multiselect attributes depends on "Fields Enclosure" parameter
     *
     * @param string $values
     * @param string $delimiter
     * @return array
     * @since 100.1.2
     */
    public function parseMultiselectValues($values, $delimiter = self::PSEUDO_MULTI_LINE_SEPARATOR)
    {
        if (strpos($values, "ecc-multi-sep") !== false) {
            return explode('ecc-multi-sep', $values);
        }
        if (empty($this->_parameters[Import::FIELDS_ENCLOSURE])) {
            return explode($delimiter, $values);
        }
        if (preg_match_all('~"((?:[^"]|"")*)"~', $values, $matches)) {
            return $values = array_map(
                function ($value) {
                    return str_replace('""', '"', $value);
                },
                $matches[1]
            );
        }
        return [$values];
    }

    /**
     * Initialize product type models.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initTypeModels()
    {
        $productTypes = $this->_importConfig->getEntityTypes($this->getEntityTypeCode());
        foreach ($productTypes as $productTypeName => $productTypeConfig) {
            if ($productTypeName == 'configurable') {
                $productTypeConfig['model'] = 'Epicor\Comm\Model\Import\Type\Configurable';
            }
            $params = [$this, $productTypeName];
            if (!($model = $this->_productTypeFactory->create($productTypeConfig['model'], ['params' => $params]))
            ) {
                throw new LocalizedException(
                    __('Entity type model \'%1\' is not found', $productTypeConfig['model'])
                );
            }
            if (!$model instanceof \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType) {
                throw new LocalizedException(
                    __(
                        'Entity type model must be an instance of '
                        . \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType::class
                    )
                );
            }
            if ($model->isSuitable()) {
                $this->_productTypeModels[$productTypeName] = $model;
            }
            // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $this->_fieldsMap = array_merge($this->_fieldsMap, $model->getCustomFieldsMapping());
            $this->_specialAttributes = array_merge($this->_specialAttributes, $model->getParticularAttributes());
            // phpcs:enable
        }
        $this->_initErrorTemplates();
        // remove doubles
        $this->_specialAttributes = array_unique($this->_specialAttributes);

        return $this;
    }

    /**
     * Delete products.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product
     * @throws \Exception
     */
    protected function _deleteProducts()
    {
        $productEntityTable = $this->_resourceFactory->create()->getEntityTable();
        $idsToDelete = [];
        foreach ($this->_deleteProducts as $rowNum => $rowData) {
            if ($this->validateRow($rowData, $rowNum) && self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                $idsToDelete[] = $this->getExistingSku($rowData[self::COL_SKU])['entity_id'];
            }
        }
        if ($idsToDelete) {
            $this->countItemsDeleted += count($idsToDelete);
            $this->transactionManager->start($this->_connection);
            try {
                $this->objectRelationProcessor->delete(
                    $this->transactionManager,
                    $this->_connection,
                    $productEntityTable,
                    $this->_connection->quoteInto('entity_id IN (?)', $idsToDelete),
                    ['entity_id' => $idsToDelete]
                );
                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $idsToDelete,
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]);
                $this->transactionManager->commit();
            } catch (\Exception $e) {
                $this->transactionManager->rollBack();
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Gather and save information about product entities.
     *
     * @param array $_productDataArray Product data.
     * @param array $websitesCache Website data.
     * @param array $_processNewattributeOption New Attribute Option Data data.
     * @param array $_newAttribute New Attribute data.
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @throws LocalizedException
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function saveProducts($websitesCache, $datas, $processNewattribute = false, $newAttribute = [])
    {
        $priceIsGlobal = $this->_catalogData->isPriceGlobal();
        $productLimit = null;
        $productsQty = null;
        $entityLinkField = $this->getProductEntityLinkField();
        $this->websitesCache = $websitesCache;
        try {
            //$this->_logger->info(print_r($datas, true));
            $bunch = $datas;
            $entityRowsIn = [];
            $entityRowsUp = [];
            $attributes = [];
            $this->categoriesCache = [];
            $tierPrices = [];
            $mediaGallery = [];
            $labelsForUpdate = [];
            $imagesForChangeVisibility = [];
            $uploadedImages = [];
            $previousType = null;
            $prevAttributeSet = null;
            //$existingImages = $this->getExistingImages($bunch);
            $rowNum = 1;
            foreach ($bunch as $rowData) {
                //if new option was added start

                $isNewAttributeSet = false;
                //if new attribute is getting created need to reload the data
                if (isset($newAttribute['new_attribute_set'])) {
                    unset($newAttribute['new_attribute_set']);
                    $this->_initAttributeSets();
                    $this->_initTypeModels($rowData[self::COL_TYPE]);
                    $isNewAttributeSet = true;
                }
                //if new attribute option is added need to add exiting values
                if ($processNewattribute) {
                    if (isset($processNewattribute['new_tax_class'])) {
                        unset($processNewattribute['new_tax_class']);
                    }
                    $rowScope = $this->getRowScope($rowData);
                    $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
                    if ($productType !== null) {
                        $previousType = $productType;
                    }
                    if (isset($rowData[self::COL_ATTR_SET])) {
                        $prevAttributeSet = $rowData[self::COL_ATTR_SET];
                    }
                    if (self::SCOPE_NULL == $rowScope) {
                        // for multiselect attributes only
                        if ($prevAttributeSet !== null) {
                            $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
                        }
                        if ($productType === null && $previousType !== null) {
                            $productType = $previousType;
                        }
                        if ($productType === null) {
                            continue;
                        }
                    }
                    $productTypeModel = $this->_productTypeModels[$productType];
                    $productTypeModel->__destruct();
                    $this->_initTypeModels($rowData[self::COL_TYPE]);
                }
                //Validate The data
                if (!$this->validateRow($rowData, $rowNum)) {
                    foreach ($this->validator->getMessages() as $message) {
                        $error = $rowData['sku'] . ' --- ' . $message;
                        $this->_logger->info($rowData['sku'] . ' --- ' . $message);
                        throw new \Exception($error);
                    }
                    $this->_logger->info(print_r($this->getErrorAggregator()->getAllErrors(), true));
                    foreach ($this->getErrorAggregator()->getAllErrors() as $errors) {
                        throw new \Exception($errors->getErrorMessage());
                    }
                    continue;
                }

                $rowScope = $this->getRowScope($rowData);
                $rowSku = $rowData[self::COL_SKU];
                $urlKey = $this->getUrlKey($rowData);
                if (!empty($rowData[self::URL_KEY])) {
                    // If url_key column and its value were in the CSV file
                    $rowData[self::URL_KEY] = $urlKey;
                } elseif ($this->isNeedToChangeUrlKey($rowData)) {
                    // If url_key column was empty or even not declared in the CSV file but by the rules it is need to
                    // be setteed. In case when url_key is generating from name column we have to ensure that the bunch
                    // of products will pass for the event with url_key column.
                    if ($urlKey) {
                        $datas[$rowSku][self::URL_KEY] = $rowData[self::URL_KEY] = $urlKey;
                    }
                }

                if (null === $rowSku) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    $this->_logger->info(print_r($this->getErrorAggregator()->getAllErrors(), true));
                    foreach ($this->getErrorAggregator()->getAllErrors() as $errors) {
                        throw new \Exception($errors->getErrorMessage());
                    }
                    continue;
                }

                if (self::SCOPE_STORE == $rowScope) {
                    // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE] = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                    $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
                }

                // 1. Entity phase
                if ($this->isSkuExist($rowSku)) {
                    // existing row
                    if (isset($rowData['attribute_set_code'])) {
                        $attributeSetId = $this->catalogConfig->getAttributeSetId(
                            $this->getEntityTypeId(),
                            $rowData['attribute_set_code']
                        );
                        // wrong attribute_set_code was received
                        if (!$attributeSetId) {
                            throw new LocalizedException(
                                __(
                                    'Wrong attribute set code "%1", please correct it and try again.',
                                    $rowData['attribute_set_code']
                                )
                            );
                        }
                    } else {
                        $attributeSetId = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    }

                    $entityRowsUp[] = [
                        'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        'attribute_set_id' => $attributeSetId,
                        $entityLinkField => $this->getExistingSku($rowSku)[$entityLinkField]
                    ];
                } else {
                    $entityRowsIn[strtolower($rowSku)] = [
                        'attribute_set_id' => $this->skuProcessor->getNewSku($rowSku)['attr_set_id'],
                        'type_id' => $this->skuProcessor->getNewSku($rowSku)['type_id'],
                        'sku' => $rowSku,
                        'has_options' => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
                        'created_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                        'updated_at' => (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT),
                    ];
                    $productsQty++;
                }
                if (!array_key_exists($rowSku, $this->websitesCache)) {
                    $this->websitesCache[$rowSku] = [];
                }

                // 2. Product-to-Website phase
                if (!empty($rowData[self::COL_PRODUCT_WEBSITES])) {
                    $websiteCodes = explode($this->getMultipleValueSeparator(), $rowData[self::COL_PRODUCT_WEBSITES]);
                    foreach ($websiteCodes as $websiteCode) {
                        $websiteId = $this->storeResolver->getWebsiteCodeToId($websiteCode);
                        $this->websitesCache[$rowSku][$websiteId] = true;
                    }
                } else {
                    $product = $this->retrieveProductBySku($rowSku);
                    if ($product) {
                        $websiteIds = $product->getWebsiteIds();
                        foreach ($websiteIds as $websiteId) {
                            $this->websitesCache[$rowSku][$websiteId] = true;
                        }
                    }
                }

                // 3. Categories phase
                if (!array_key_exists($rowSku, $this->categoriesCache)) {
                    $this->categoriesCache[$rowSku] = [];
                }
                $rowData['rowNum'] = $rowNum;
                $categoryIds = $this->processRowCategories($rowData);
                foreach ($categoryIds as $id) {
                    $this->categoriesCache[$rowSku][$id] = true;
                }
                unset($rowData['rowNum']);

                // 4.1. Tier prices phase
                if (!empty($rowData['_tier_price_website'])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] ==
                        self::VALUE_ALL ? 0 : $rowData['_tier_price_customer_group'],
                        'qty' => $rowData['_tier_price_qty'],
                        'value' => $rowData['_tier_price_price'],
                        'website_id' => self::VALUE_ALL == $rowData['_tier_price_website'] ||
                        $priceIsGlobal ? 0 : $this->storeResolver->getWebsiteCodeToId($rowData['_tier_price_website']),
                    ];
                }


                if (!$this->validateRow($rowData, $rowNum)) {
                    foreach ($this->validator->getMessages() as $message) {
                        $error = $rowData['sku'] . ' --- ' . $message;
                        $this->_logger->info($rowData['sku'] . ' --- ' . $message);
                        throw new \Exception($error);
                    }
                    $this->_logger->info(print_r($this->getErrorAggregator()->getAllErrors(), true));
                    foreach ($this->getErrorAggregator()->getAllErrors() as $errors) {
                        throw new \Exception($errors->getErrorMessage());
                    }

                    continue;
                }

                // 6. Attributes phase
                $rowStore = (self::SCOPE_STORE == $rowScope)
                    ? $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
                    : 0;
                $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
                if ($productType !== null) {
                    $previousType = $productType;
                }
                if (isset($rowData[self::COL_ATTR_SET])) {
                    $prevAttributeSet = $rowData[self::COL_ATTR_SET];
                }
                if (self::SCOPE_NULL == $rowScope) {
                    // for multiselect attributes only
                    if ($prevAttributeSet !== null) {
                        $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
                    }
                    if ($productType === null && $previousType !== null) {
                        $productType = $previousType;
                    }
                    if ($productType === null) {
                        continue;
                    }
                }
                $productTypeModel = $this->_productTypeModels[$productType];
                if (!empty($rowData['tax_class_name'])) {
                    $rowData['tax_class_id'] =
                        $this->taxClassProcessor->upsertTaxClass($rowData['tax_class_name'], $productTypeModel);
                }

                $brandCode = 'ecc_brand_updated';
                if (isset($rowData[$brandCode])) {
                    $brandValue = $rowData[$brandCode];
                }

                $rowData = $productTypeModel->prepareAttributesWithDefaultValueForSave(
                    $rowData,
                    !$this->isSkuExist($rowSku)
                );

                if (isset($rowData[$brandCode])) {
                    $rowData[$brandCode] = $this->attributeOptions->getOptionLabelValue($brandCode, $brandValue);
                }

                $product = $this->_proxyProdFactory->create(['data' => $rowData]);

                foreach ($rowData as $attrCode => $attrValue) {
                    if ($processNewattribute && ($key = array_search($attrCode, $processNewattribute)) !== false) {
                        unset($processNewattribute[$key]);
                    }
                    $attribute = $this->retrieveAttributeByCode($attrCode);
                    if ('multiselect' != $attribute->getFrontendInput() && self::SCOPE_NULL == $rowScope) {
                        // skip attribute processing for SCOPE_NULL rows
                        continue;
                    }
                    $attrId = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds = [0];
                    if ('datetime' == $attribute->getBackendType()
                        && (
                            in_array($attribute->getAttributeCode(), $this->dateAttrCodes)
                            || $attribute->getIsUserDefined()
                        )
                    ) {
                        $attrValue = $this->dateTime->formatDate($attrValue, false);
                    } elseif ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                        $attrValue = gmdate(
                            'Y-m-d H:i:s',
                            $this->_localeDate->date($attrValue)->getTimestamp()
                        );
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->storeResolver->getStoreIdToWebsiteStoreIds($rowStore);
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = [$rowStore];
                        }
                        if (!$this->isSkuExist($rowSku)) {
                            $storeIds[] = 0;
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        if (!isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
                            $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                        }
                    }
                    // restore 'backend_model' to avoid 'default' setting
                    $attribute->setBackendModel($backModel);
                }
                $rowNum++;
            }
            $importTable = $this->_connection->getTableName('importexport_importdata');
            $this->_connection->query('delete from ' . $importTable);
            $this->_dataSourceModel->saveBunch('catalog_product', 'add_update', $datas);
            $lastId = $this->_connection->lastInsertId($importTable);
            $this->saveProductEntity(
                $entityRowsIn,
                $entityRowsUp
            )->_saveProductWebsites(
                $websitesCache
            )->_saveProductAttributes(
                $attributes
            );
            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_after_eccstk',
                ['adapter' => $this, 'bunch' => $datas]
            );
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw $e;
        }
        return $lastId;
    }


    /**
     * Retrieve url key from provided row data.
     *
     * @param array $rowData
     * @return string
     *
     * @since 100.0.3
     */
    protected function getUrlKey($rowData)
    {
        if (!empty($rowData[self::URL_KEY])) {
            $urlKey = (string)$rowData[self::URL_KEY];
            return trim(strtolower($urlKey));
        }

        if (!empty($rowData[self::COL_NAME])
            && (array_key_exists(self::URL_KEY, $rowData) || !$this->isSkuExist($rowData[self::COL_SKU]))) {
            $urlKeyToFormat = $rowData[self::COL_NAME].$rowData[self::COL_SKU];
            if ($this->commMessagingHelper->checkUomHasSpecialCharacter($rowData[self::COL_SKU])) {
                $urlKeyToFormat = $rowData[self::COL_NAME].$rowData[self::COL_SKU].rand();
            }
            return $this->productUrl->formatUrlKey($urlKeyToFormat);
        }

        return '';
    }



    /**
     * Save product websites.
     *
     * @param array $websiteData
     * @return \Magento\CatalogImportExport\Model\Import\Product
     */
    protected function _saveProductWebsites(array $websiteData)
    {
        static $tableName = null;

        if (!$tableName) {
            $tableName = $this->_resourceFactory->create()->getProductWebsiteTable();
        }
        if ($websiteData) {
            $websitesData = [];
            $delProductId = [];

            foreach ($websiteData as $delSku => $websites) {
                $productId = $this->getExistingSku($delSku)['entity_id'];
                $delProductId[] = $productId;

                foreach (array_keys($websites) as $websiteId) {
                    $websitesData[] = ['product_id' => $productId, 'website_id' => $websiteId];
                }
            }
            if (Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $tableName,
                    $this->_connection->quoteInto('product_id IN (?)', $delProductId)
                );
            }
            if ($websitesData) {
                $this->_connection->insertOnDuplicate($tableName, $websitesData);
            }
        }
        return $this;
    }

    /**
     * Whether a url key is needed to be change.
     *
     * @param array $rowData
     * @return bool
     */
    private function isNeedToChangeUrlKey(array $rowData): bool
    {
        $urlKey = $this->getUrlKey($rowData);
        $productExists = $this->isSkuExist($rowData[self::COL_SKU]);
        $markedToEraseUrlKey = isset($rowData[self::URL_KEY]);
        // The product isn't new and the url key index wasn't marked for change.
        if (!$urlKey && $productExists && !$markedToEraseUrlKey) {
            // Seems there is no need to change the url key
            return false;
        }

        return true;
    }

    /**
     * Check if product exists for specified SKU
     *
     * @param string $sku
     * @return bool
     */
    private function isSkuExist($sku)
    {
        $sku = strtolower($sku);
        return isset($this->_oldSku[$sku]);
    }

    /**
     * Get existing product data for specified SKU
     *
     * @param string $sku
     * @return array
     */
    private function getExistingSku($sku)
    {
        return $this->_oldSku[strtolower($sku)];
    }

    /**
     * Retrieve product by sku.
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function retrieveProductBySku($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $product;
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Retrieve instance of product custom options import entity
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Option
     */
    public function getOptionEntity()
    {
        return $this->_optionEntity;
    }


    /**
     * Custom Options saving.
     *
     * @return $this
     */
    public function saveCustomOptions($datas)
    {
        $this->getOptionEntity()->importNewData($datas);
    }


    /**
     * Location saving.
     *
     * @return $this
     */
    public function saveLocationsData($datas)
    {
        $tableName = $this->_connection->getTableName('ecc_location_product');
        $currencytableName = $this->_connection->getTableName('ecc_location_product_currency');
        foreach ($datas as $rowNum => $rowData) {
            $sku = $rowData[self::COL_SKU];
            if ($this->skuProcessor->getNewSku($sku) !== null) {
                $productId = $this->getExistingSku($sku)['entity_id'];
                if (isset($rowData['new_locations'])) {
                    $insertLocations = [];
                    $insertCurrencies = [];
                    $deleteCurrencies = [];
                    foreach ($rowData['new_locations'] as $location) {
                        $currencies = [];
                        isset($location['currencies']) ? $currencies = $location['currencies'] : $currencies = [];
                        if (isset($currencies['delete_currencies'])) {
                            $deleteCurrencies = $currencies['delete_currencies'];
                            unset($currencies['delete_currencies']);
                        }
                        unset($location['currencies']);
                        $location['product_id'] = $productId;
                        $location['id'] = isset($location['id']) ? $location['id'] : '';
                        $insertLocations[] = $location;
                        foreach ($currencies as $currencie) {
                            $currencie['product_id'] = $productId;
                            $currencie['id'] = isset($currencie['id']) ? $currencie['id'] : '';
                            $insertCurrencies[] = $currencie;
                        }
                    }
                    if (!empty($insertLocations))
                        $this->_connection->insertOnDuplicate($tableName, $insertLocations);
                    if (!empty($insertCurrencies))
                        $this->_connection->insertOnDuplicate($currencytableName, $insertCurrencies);
                }

                if (isset($rowData['delete_locations'])) {
                    foreach ($rowData['delete_locations'] as $deleteLocation) {
                        $this->_connection->delete($tableName, $deleteLocation);
                        $deleteCurrencies[] = ['product_id = ?' => $deleteLocation['product_id = ?'], 'location_code = ?' => $deleteLocation['location_code = ?']];
                    }
                }
                if (!empty($deleteCurrencies)) {
                    foreach ($deleteCurrencies as $deleteCurrencie) {
                        $this->_connection->delete($currencytableName, $deleteCurrencie);
                    }
                }
            }
        }
    }


    /**
     * Manufacturers  saving.
     *
     * @return $this
     */
    public function saveManufacturersData($datas)
    {
        $action = $this->catalogResourceModelProductActionFactory->create();
        foreach ($datas as $rowNum => $rowData) {
            $sku = $rowData[self::COL_SKU];
            if ($this->skuProcessor->getNewSku($sku) !== null) {
                $productId = $this->getExistingSku($sku)['entity_id'];
                if (isset($rowData['ecc_manufacturers_ecc_data'])) {
                    $changes['ecc_manufacturers'] = $rowData['ecc_manufacturers_ecc_data'];
                    $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                    $action->updateAttributes(array($productId), $changes, $storeId);
                }
            }
        }
    }

    /**
     * Saving parentProductdata.
     *
     * @return $this
     */
    public function saveParentProductData($datas)
    {
        $action = $this->catalogResourceModelProductActionFactory->create();
        foreach ($datas as $rowNum => $rowData) {
            $sku = $rowData[self::COL_SKU];
            $productType = $rowData[self::COL_TYPE];
            $storeId = !empty($rowData[self::COL_STORE])
                ? $this->getStoreIdByCode($rowData[self::COL_STORE])
                : Store::DEFAULT_STORE_ID;

            if ($productType == 'grouped' && $this->skuProcessor->getNewSku($sku) !== null) {
                $productId = $this->getExistingSku($sku)['entity_id'];
                $changes = [];
                if (isset($rowData['price'])) {
                    $changes['price'] = $rowData['price'];
                }
                if (isset($rowData['cost'])) {
                    $changes['cost'] = $rowData['cost'];
                }
                if (isset($rowData['tax_class_id'])) {
                    $changes['tax_class_id'] = $rowData['tax_class_id'];
                }
                if (!empty($changes)) {
                    $action->updateAttributes(array($productId), $changes, $storeId);
                }

            }
        }
    }


    /**
     * Invalidate Index.
     *
     * @return $this
     */
    public function invalidateIndex()
    {
        $relatedIndexers = ['catalog_product_price', 'catalogrule_rule', 'inventory', 'catalogsearch_fulltext',
            'catalog_category_product', 'cataloginventory_stock', 'catalog_product_attribute',
            'catalog_product_category'];
        foreach ($relatedIndexers as $indexerId) {
            try {
                $indexer = $this->indexerRegistry->get($indexerId);
                if (!$indexer->isScheduled()) {
                    $indexer->invalidate();
                } else {
                    if ($indexerId == \Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID) {
                        $this->indexerState->loadByIndexer($indexerId);
                        $this->indexerState->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID);
                        $this->indexerState->save();
                    }
                }
            } catch (\InvalidArgumentException $e) {
            }
        }

    }

    /**
     * Get row stock item model
     *
     * @param array $rowData
     * @return StockItemInterface
     */
    private function getRowExistingStockItem(array $rowData): StockItemInterface
    {
        $productId = $this->skuProcessor->getNewSku($rowData[self::COL_SKU])['entity_id'];
        $websiteId = $this->stockConfiguration->getDefaultScopeId();
        return $this->stockRegistry->getStockItem($productId, $websiteId);
    }


    /**
     * Stock item saving.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product
     */
    protected function _saveStockItem()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $stockData = [];
            $productIdsToReindex = [];
            $stockChangedProductIds = [];
            // Format bunch to stock data rows
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }

                $row = [];
                $sku = $rowData[self::COL_SKU];
                if ($this->skuProcessor->getNewSku($sku) !== null) {
                    $stockItem = $this->getRowExistingStockItem($rowData);
                    $existingStockItemData = $stockItem->getData();
                    $row = $this->formatStockDataForRow($rowData);
                    $productIdsToReindex[] = $row['product_id'];
                    $storeId = $this->getRowStoreId($rowData);
                    if (!empty(array_diff_assoc($row, $existingStockItemData))
                        || $this->statusProcessor->isStatusChanged($sku, $storeId)
                    ) {
                        $stockChangedProductIds[] = $row['product_id'];
                    }
                }

                if (!isset($stockData[$sku])) {
                    $stockData[$sku] = $row;
                }
            }

            // Insert rows
            if (!empty($stockData)) {
                $this->stockItemImporter->import($stockData);
            }

            $this->reindexStockStatus($stockChangedProductIds);
            $this->reindexProducts($productIdsToReindex);
        }
        return $this;
    }


    /**
     * Reindex stock status for provided product IDs
     *
     * @param array $productIds
     */
    private function reindexStockStatus(array $productIds): void
    {
        if ($productIds) {
            $this->stockProcessor->reindexList($productIds);
        }
    }

    /**
     * Initiate product reindex by product ids
     *
     * @param array $productIdsToReindex
     * @return void
     */
    private function reindexProducts($productIdsToReindex = [])
    {
        $indexer = $this->indexerRegistry->get('catalog_product_category');
        if (is_array($productIdsToReindex) && count($productIdsToReindex) > 0 && !$indexer->isScheduled()) {
            $indexer->reindexList($productIdsToReindex);
        }
    }

    /**
     * Get row store ID
     *
     * @param array $rowData
     * @return int
     */
    private function getRowStoreId(array $rowData): int
    {
        return !empty($rowData[self::COL_STORE])
            ? (int)$this->getStoreIdByCode($rowData[self::COL_STORE])
            : Store::DEFAULT_STORE_ID;
    }

    /**
     * Format row data to DB compatible values.
     *
     * @param array $rowData
     * @return array
     */
    private function formatStockDataForRow(array $rowData): array
    {
        $sku = $rowData[self::COL_SKU];
        $row['product_id'] = $this->skuProcessor->getNewSku($sku)['entity_id'];
        $row['website_id'] = $this->stockConfiguration->getDefaultScopeId();
        $row['stock_id'] = $this->stockRegistry->getStock($row['website_id'])->getStockId();

        $stockItemDo = $this->stockRegistry->getStockItem($row['product_id'], $row['website_id']);
        $existStockData = $stockItemDo->getData();

        if (isset($rowData['qty']) && $rowData['qty'] == 0 && !isset($rowData['is_in_stock'])) {
            $rowData['is_in_stock'] = 0;
        }

        $row = array_merge(
            $this->defaultStockData,
            array_intersect_key($existStockData, $this->defaultStockData),
            array_intersect_key($rowData, $this->defaultStockData),
            $row
        );
        if (isset($existStockData['item_id'])) {
            $row['item_id'] = $existStockData['item_id'];
        } else {
            $row['item_id'] = 0;
        }
        if ($this->stockConfiguration->isQty($this->skuProcessor->getNewSku($sku)['type_id'])) {
            $stockItemDo->setData($row);
            $row['is_in_stock'] = $row['is_in_stock'] ?? $this->stockStateProvider->verifyStock($stockItemDo);
            if ($this->stockStateProvider->verifyNotification($stockItemDo)) {
                $date = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
                $row['low_stock_date'] = $date->format(DateTime::DATETIME_PHP_FORMAT);
            }
            $row['stock_status_changed_auto'] = (int)!$this->stockStateProvider->verifyStock($stockItemDo);
        } else {
            $row['qty'] = 0;
        }

        return $row;
    }

}
