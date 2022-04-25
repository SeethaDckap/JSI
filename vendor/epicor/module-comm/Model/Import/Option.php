<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Import;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as ProductOptionValueCollection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as ProductOptionValueCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Store\Model\Store;

/**
 * Used Extended Class for import
 * Custom type are added so to pass the validation EWA
 * Remove Custom options
 * Added value to custom fields eg. ecc_code
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Option extends \Magento\CatalogImportExport\Model\Import\Product\Option
{


    /**
     * @var string
     */
    private $columnMaxCharacters = '_custom_option_max_characters';

    /**
     * List of specific custom option types
     *
     * @var array
     */
    protected $_specificTypes = [
        'date' => ['price', 'sku'],
        'date_time' => ['price', 'sku'],
        'time' => ['price', 'sku'],
        'field' => ['price', 'sku', 'max_characters'],
        'area' => ['price', 'sku', 'max_characters'],
        'drop_down' => true,
        'radio' => true,
        'checkbox' => true,
        'multiple' => true,
        'ecc_text_field' => [],
        'ewa_description' => [],
        'ewa_code' => [],
        'ewa_sku' => [],
        'ewa_title' => [],
        'ewa_short_description' => [],
        'file' => ['sku', 'file_extension', 'image_size_x', 'image_size_y'],
    ];
    /**
     * @var array
     */
    private $optionTypeTitles;

    /**
     * @var array
     */
    private $lastOptionTitle;

    /**
     * @var ProductOptionValueCollectionFactory
     */
    private $productOptionValueCollectionFactory;

    public function __construct(
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
        ProcessingErrorAggregatorInterface $errorAggregator,
        array $data = [],
        ProductOptionValueCollectionFactory $productOptionValueCollectionFactory = null
    )
    {
        $this->_resource = $resource;
        $this->_catalogData = $catalogData;
        $this->_storeManager = $_storeManager;
        $this->_productFactory = $productFactory;
        $this->_dataSourceModel = $importData;
        $this->_optionColFactory = $optionColFactory;
        $this->_colIteratorFactory = $colIteratorFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->productOptionValueCollectionFactory = $productOptionValueCollectionFactory
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(ProductOptionValueCollectionFactory::class);

        if (isset($data['connection'])) {
            $this->_connection = $data['connection'];
        } else {
            $this->_connection = $resource->getConnection();
        }

        if (isset($data['resource_helper'])) {
            $this->_resourceHelper = $data['resource_helper'];
        } else {
            $this->_resourceHelper = $resourceHelper;
        }

        if (isset($data['is_price_global'])) {
            $this->_isPriceGlobal = $data['is_price_global'];
        } else {
            $this->_isPriceGlobal = $this->_catalogData->isPriceGlobal();
        }

        /**
         * TODO: Make metadataPool a direct constructor dependency, and eliminate its setter & getter
         */
        if (isset($data['metadata_pool'])) {
            $this->metadataPool = $data['metadata_pool'];
        }

        $this->errorAggregator = $errorAggregator;

        $this->_initSourceEntities($data)->_initTables($data)->_initStores($data);

        $this->_initMessageTemplates();

        $this->_initProductsSku()->_initOldCustomOptions();
        parent::__construct(
            $importData,
            $resource,
            $resourceHelper,
            $_storeManager,
            $productFactory,
            $optionColFactory,
            $colIteratorFactory,
            $catalogData,
            $scopeConfig,
            $dateTime,
            $errorAggregator,
            $data,
            $productOptionValueCollectionFactory
        );
    }

    /**
     * Retrieve option data
     *
     * @param array $rowData
     * @param int $productId
     * @param int $optionId
     * @param string $type
     * @return array
     */
    protected function _getOptionData(array $rowData, $productId, $optionId, $type)
    {
        $optionData = [
            'option_id' => $optionId,
            'sku' => '',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x' => 0,
            'image_size_y' => 0,
            'product_id' => $productId,
            'type' => $type,
            'is_require' => empty($rowData[self::COLUMN_IS_REQUIRED]) ? 0 : 1,
            'ecc_code' => empty($rowData['ecc_code']) ? '' : $rowData['ecc_code'],
            'ecc_default_value' => empty($rowData['ecc_default_value']) ? '' : $rowData['ecc_default_value'],
            'max_characters' => empty($rowData['max_characters']) ? '' : $rowData['max_characters'],
            'ecc_validation_code' => empty($rowData['ecc_validation_code']) ? '' : $rowData['ecc_validation_code'],
            'sort_order' => empty($rowData[self::COLUMN_SORT_ORDER]) ? 0 : abs($rowData[self::COLUMN_SORT_ORDER]),
        ];

        if (!$this->_isRowHasSpecificType($type)) {
            // simple option may have optional params
            foreach ($this->_specificTypes[$type] as $paramSuffix) {
                if (isset($rowData[self::COLUMN_PREFIX . $paramSuffix])) {
                    $data = $rowData[self::COLUMN_PREFIX . $paramSuffix];

                    if (array_key_exists($paramSuffix, $optionData)) {
                        $optionData[$paramSuffix] = $data;
                    }
                }
            }
        }
        return $optionData;
    }


    /**
     * Import data rows.
     *
     * Additional store view data (option titles) will be sought in store view specified import file rows
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function importNewData($datas)
    {
        $this->_initProductsSku();
        $nextOptionId = $this->_resourceHelper->getNextAutoincrement($this->_tables['catalog_product_option']);
        $nextValueId = $this->_resourceHelper->getNextAutoincrement(
            $this->_tables['catalog_product_option_type_value']
        );
        $prevOptionId = 0;
        $optionId = null;
        $valueId = null;
        $processedSkus = [];
        foreach ($datas as $newdata) {
            if(isset($processedSkus[$newdata[self::COLUMN_SKU]])){
                continue;
            }
            $processedSkus[$newdata[self::COLUMN_SKU]] = $newdata[self::COLUMN_SKU];
            $bunch[] = $newdata;
            $products = [];
            $options = [];
            $removeOptions = [];
            $titles = [];
            $removeTitles=[];
            $prices = [];
            $typeValues = [];
            $typePrices = [];
            $typeTitles = [];
            $parentCount = [];
            $childCount = [];
            $optionsToRemove = [];
            foreach ($bunch as $rowNumber => $rowData) {
                if (isset($optionId, $valueId) &&
                    (empty($rowData[PRODUCT::COL_STORE_VIEW_CODE]) || empty($rowData['custom_options']))
                ) {
                    $nextOptionId = $optionId;
                    $nextValueId = $valueId;
                }
                $optionId = $nextOptionId;
                $valueId = $nextValueId;
                $multiRowData = $this->_getMultiRowFormat($rowData);
                if (!empty($rowData[self::COLUMN_SKU]) && isset($this->_productsSkuToId[$rowData[self::COLUMN_SKU]])) {
                    $this->_rowProductId = $this->_productsSkuToId[$rowData[self::COLUMN_SKU]];
                    if (array_key_exists('custom_options', $rowData)
                        && (
                            trim($rowData['custom_options']) === '' ||
                            trim($rowData['custom_options']) === $this->_productEntity->getEmptyAttributeValueConstant()
                        )
                    ) {
                        $optionsToRemove[] = $this->_rowProductId;
                    }
                }


                $currencytableName = $this->_connection->getTableName('catalog_product_option');
                if (isset($rowData['remove_custom_options']) && !empty($rowData['remove_custom_options'])) {
                    $newrowData = $rowData;
                    $newrowData['custom_options'] = $rowData['remove_custom_options'];
                    $multiRowData2 = $this->_getMultiRowFormat($newrowData);
                    foreach ($multiRowData2 as $combinedData2) {
                        foreach ($newrowData as $key => $field) {
                            $combinedData2[$key] = $field;
                        }
                        if (!$this->_parseRequiredData($combinedData2)
                        ) {
                            continue;
                        }
                        foreach ($newrowData as $key => $field) {
                            $combinedData2[$key] = $field;
                        }
                        $removeoptionData = $this->_collectOptionMainData(
                            $combinedData2,
                            $prevOptionId,
                            $optionId,
                            $products,
                            $prices
                        );
                        if ($removeoptionData != null) {
                            $removeOptions[] = $removeoptionData;
                        }

                        $this->_collectOptionTitle($combinedData2, $prevOptionId, $removeTitles);
                        foreach ($removeOptions as &$removeoptionInfo) {
                            $newoptionId = $removeoptionInfo['option_id'];
                            $removeTitlesinfo = $removeTitles[$newoptionId];
                            if ($optionId = $this->_findExistingOptionId($removeoptionInfo, $removeTitlesinfo)) {
                                $this->_connection->delete($currencytableName, ['option_id = ?' => $optionId]);
                            }
                        }

                    }
                }

                foreach ($multiRowData as $combinedData) {
                    foreach ($rowData as $key => $field) {
                        $combinedData[$key] = $field;
                    }
                    if (!$this->_parseRequiredData($combinedData)
                    ) {
                        continue;
                    }

                    $optionData = $this->_collectOptionMainData(
                        $combinedData,
                        $prevOptionId,
                        $optionId,
                        $products,
                        $prices
                    );

                    if ($optionData != null) {
                        $options[] = $optionData;
                    }
                    $this->_collectOptionTypeData(
                        $combinedData,
                        $prevOptionId,
                        $valueId,
                        $typeValues,
                        $typePrices,
                        $typeTitles,
                        $parentCount,
                        $childCount
                    );

                    $this->_collectOptionTitle($combinedData, $prevOptionId, $titles);
                    $this->checkOptionTitles(
                        $options,
                        $titles,
                        $combinedData,
                        $prevOptionId,
                        $optionId,
                        $products,
                        $prices
                    );
                }
            }
            $this->removeExistingOptions($products, $optionsToRemove);
            $types = [
                'values' => $typeValues,
                'prices' => $typePrices,
                'titles' => $typeTitles,
            ];
            $this->setLastOptionTitle($titles);

            //Save prepared custom options data.
            $this->savePreparedCustomOptions(
                $products,
                $options,
                $titles,
                $prices,
                $types
            );
        }
        return true;
    }

    /**
     * Get multiRow format from one line data.
     *
     * @param array $rowData
     * @return array
     */
    protected function _getMultiRowFormat($rowData)
    {
        // Parse custom options.
        $rowData = $this->_parseCustomOptions($rowData);
        $multiRow = [];
        if (empty($rowData['custom_options']) || !is_array($rowData['custom_options'])) {
            return $multiRow;
        }

        $i = 0;
        foreach ($rowData['custom_options'] as $name => $customOption) {
            $i++;
            foreach ($customOption as $rowOrder => $optionRow) {

                $row = [
                    self::COLUMN_STORE => '',
                    self::COLUMN_TITLE => $name,
                    self::COLUMN_SORT_ORDER => $i,
                    self::COLUMN_ROW_SORT => $rowOrder
                ];
                foreach ($this->processOptionRow($name, $optionRow) as $key => $value) {
                    $row[$key] = $value;
                }
                $name = '';
                $multiRow[] = $row;
            }
        }

        return $multiRow;
    }

    /**
     * Adds price data.
     *
     * @param array $result
     * @param array $optionRow
     * @return array
     */
    private function addPriceData(array $result, array $optionRow): array
    {
        if (isset($optionRow['price'])) {
            $percent_suffix = '';
            if (isset($optionRow['price_type']) && $optionRow['price_type'] == 'percent') {
                $percent_suffix = '%';
            }
            $result[self::COLUMN_ROW_PRICE] = $optionRow['price'] . $percent_suffix;
        }

        $result[self::COLUMN_PREFIX . 'price'] = $result[self::COLUMN_ROW_PRICE];

        return $result;
    }

    /**
     * Process option row.
     *
     * @param string $name
     * @param array $optionRow
     * @return array
     */
    private function processOptionRow($name, $optionRow)
    {
        $result = [
            self::COLUMN_TYPE => $name ? $optionRow['type'] : '',
            self::COLUMN_ROW_TITLE => '',
            self::COLUMN_ROW_PRICE => ''
        ];

        $result = $this->addPriceData($result, $optionRow);

        if (isset($optionRow['_custom_option_store'])) {
            $result[self::COLUMN_STORE] = $optionRow['_custom_option_store'];
        }
        if (isset($optionRow['required'])) {
            $result[self::COLUMN_IS_REQUIRED] = $optionRow['required'];
        }
        if (isset($optionRow['sku'])) {
            $result[self::COLUMN_ROW_SKU] = $optionRow['sku'];
            $result[self::COLUMN_PREFIX . 'sku'] = $optionRow['sku'];
        }
        if (isset($optionRow['option_title'])) {
            $result[self::COLUMN_ROW_TITLE] = $optionRow['option_title'];
        }

        if (isset($optionRow['max_characters'])) {
            $result['max_characters'] = $optionRow['max_characters'];
        }

        if (isset($optionRow['ecc_code'])) {
            $result['ecc_code'] = $optionRow['ecc_code'];
        }

        if (isset($optionRow['ecc_default_value'])) {
            $result['ecc_default_value'] = $optionRow['ecc_default_value'];
        }

        if (isset($optionRow['ecc_validation_code'])) {
            $result['ecc_validation_code'] = $optionRow['ecc_validation_code'];
        }

        if (isset($optionRow['sort_order'])) {
            $result['sort_order'] = $optionRow['sort_order'];
        }
        $result = $this->addFileOptions($result, $optionRow);

        return $result;
    }

    /**
     * Add file options
     *
     * @param array $result
     * @param array $optionRow
     * @return array
     */
    private function addFileOptions($result, $optionRow)
    {
        foreach (['file_extension', 'image_size_x', 'image_size_y'] as $fileOptionKey) {
            if (!isset($optionRow[$fileOptionKey])) {
                continue;
            }

            $result[self::COLUMN_PREFIX . $fileOptionKey] = $optionRow[$fileOptionKey];
        }

        return $result;
    }

    /**
     * Check options titles.
     *
     * If products were split up between bunches,
     * this function will add needed option for option titles
     *
     * @param array $options
     * @param array $titles
     * @param array $combinedData
     * @param int $prevOptionId
     * @param int $optionId
     * @param array $products
     * @param array $prices
     * @return void
     */
    private function checkOptionTitles(
        array &$options,
        array &$titles,
        array $combinedData,
        int &$prevOptionId,
        int &$optionId,
        array $products,
        array $prices
    ): void
    {
        $titlesCount = count($titles);
        if ($titlesCount > 0 && count($options) !== $titlesCount) {
            $combinedData[Product::COL_STORE_VIEW_CODE] = '';
            $optionId--;
            $option = $this->_collectOptionMainData(
                $combinedData,
                $prevOptionId,
                $optionId,
                $products,
                $prices
            );
            if ($option) {
                $options[] = $option;
            }
        }
    }

    /**
     * Checks that complex options contain values
     *
     * @param array &$options
     * @param array &$titles
     * @param array $typeValues
     * @return bool
     */
    protected function _isReadyForSaving(array &$options, array &$titles, array $typeValues)
    {
        // if complex options do not contain values - ignore them
        foreach ($options as $key => $optionData) {
            $optionId = $optionData['option_id'];
            $optionType = $optionData['type'];
            if ($this->_specificTypes[$optionType] === true && !isset($typeValues[$optionId])) {
                unset($options[$key], $titles[$optionId]);
            }
        }
        if ($options) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save prepared custom options.
     *
     * @param array $products
     * @param array $options
     * @param array $titles
     * @param array $prices
     * @param array $types
     *
     * @return void
     */
    private function savePreparedCustomOptions(
        array $products,
        array $options,
        array $titles,
        array $prices,
        array $types
    ): void
    {
        if ($this->_isReadyForSaving($options, $titles, $types['values'])) {
            if ($this->getBehavior() == Import::BEHAVIOR_APPEND) {
                $this->_compareOptionsWithExisting($options, $titles, $prices, $types['values']);
                $this->restoreOriginalOptionTypeIds($types['values'], $types['prices'], $types['titles']);
            }
            $this->_saveOptions($options)
                ->_saveTitles($titles)
                ->_savePrices($prices)
                ->_saveSpecificTypeValues($types['values'])
                ->_saveSpecificTypePrices($types['prices'])
                ->_saveSpecificTypeTitles($types['titles'])
                ->_updateProducts($products);
        }
    }

    /**
     * Restore original IDs for existing option types.
     *
     * Warning: arguments are modified by reference
     *
     * @param array $typeValues
     * @param array $typePrices
     * @param array $typeTitles
     * @return void
     */
    private function restoreOriginalOptionTypeIds(array &$typeValues, array &$typePrices, array &$typeTitles)
    {
        foreach ($typeValues as $optionId => &$optionTypes) {
            foreach ($optionTypes as &$optionType) {
                $optionTypeId = $optionType['option_type_id'];
                foreach ($typeTitles[$optionTypeId] as $storeId => $optionTypeTitle) {
                    $existingTypeId = $this->getExistingOptionTypeId($optionId, $storeId, $optionTypeTitle);
                    if ($existingTypeId) {
                        $optionType['option_type_id'] = $existingTypeId;
                        $typeTitles[$existingTypeId] = $typeTitles[$optionTypeId];
                        unset($typeTitles[$optionTypeId]);
                        if (isset($typePrices[$optionTypeId])) {
                            $typePrices[$existingTypeId] = $typePrices[$optionTypeId];
                            unset($typePrices[$optionTypeId]);
                        }
                        // If option type titles match at least in one store, consider current option type as existing
                        break;
                    }
                }
            }
        }
    }

    /**
     * Identify ID of the provided option type by its title in the specified store.
     *
     * @param int $optionId
     * @param int $storeId
     * @param string $optionTypeTitle
     * @return int|null
     */
    private function getExistingOptionTypeId($optionId, $storeId, $optionTypeTitle)
    {
        if (!isset($this->optionTypeTitles[$storeId])) {
            /** @var ProductOptionValueCollection $optionTypeCollection */
            $optionTypeCollection = $this->productOptionValueCollectionFactory->create();
            $optionTypeCollection->addTitleToResult($storeId);
            /** @var \Magento\Catalog\Model\Product\Option\Value $type */
            foreach ($optionTypeCollection as $type) {
                $this->optionTypeTitles[$storeId][$type->getOptionId()][$type->getId()] = $type->getTitle();
            }
        }
        if (isset($this->optionTypeTitles[$storeId][$optionId])
            && is_array($this->optionTypeTitles[$storeId][$optionId])
        ) {
            foreach ($this->optionTypeTitles[$storeId][$optionId] as $optionTypeId => $currentTypeTitle) {
                if ($optionTypeTitle === $currentTypeTitle) {
                    return $optionTypeId;
                }
            }
        }
        return null;
    }


    /**
     * Setting last Custom Option Title
     * to use it later in _collectOptionTitle
     * to set correct title for default store view
     *
     * @param array $titles
     */
    private function setLastOptionTitle(array &$titles): void
    {
        if (count($titles) > 0) {
            end($titles);
            $key = key($titles);
            $this->lastOptionTitle[$key] = $titles[$key];
        }
    }

    /**
     * Remove existing options.
     *
     * Remove all existing options if import behaviour is APPEND
     * in other case remove options for products with empty "custom_options" row only.
     *
     * @param array $products
     * @param array $optionsToRemove
     *
     * @return void
     */
    private function removeExistingOptions(array $products, array $optionsToRemove): void
    {
        if ($this->getBehavior() != Import::BEHAVIOR_APPEND) {
            $this->_deleteEntities(array_keys($products));
        } elseif (!empty($optionsToRemove)) {
            // Remove options for products with empty "custom_options" row
            $this->_deleteEntities($optionsToRemove);
        }
    }
}
