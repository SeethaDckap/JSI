<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Bundle\Product;


use Epicor\Comm\Model\Serialize\Serializer\Json;
use Magento\Framework\EntityManager\MetadataPool;
//use Magento\Bundle\Model\ResourceModel\Selection\Collection\FilterApplier as SelectionCollectionFilterApplier;

class Type extends \Magento\Bundle\Model\Product\Type
{

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProductHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;

    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Bundle\Model\SelectionFactory $bundleModelSelection,
        \Magento\Bundle\Model\ResourceModel\BundleFactory $bundleFactory,
        \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $bundleCollection,
        \Magento\Catalog\Model\Config $config,
        \Magento\Bundle\Model\ResourceModel\Selection $bundleSelection,
        \Magento\Bundle\Model\OptionFactory $bundleOption,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        Json $serializer = null,
        MetadataPool $metadataPool = null,
        \Magento\Catalog\Helper\Product $catalogProductHelper
    ) {
        $this->catalogProductHelper = $catalogProductHelper;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->productMetadata = $productMetadata;
        if($this->productMetadata->getVersion()<'2.2.0'){
            parent::__construct(
                $catalogProductOption,
                $eavConfig,
                $catalogProductType,
                $eventManager,
                $fileStorageDb,
                $filesystem,
                $coreRegistry,
                $logger,
                $productRepository,
                $catalogProduct,
                $catalogData,
                $bundleModelSelection,
                $bundleFactory,
                $bundleCollection,
                $config,
                $bundleSelection,
                $bundleOption,
                $storeManager,
                $priceCurrency,
                $stockRegistry,
                $stockState
            );  

        }else{       
            $selectionCollectionFilterApplier = class_exists('Magento\Bundle\Model\ResourceModel\Selection\Collection\FilterApplier')?
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Bundle\Model\ResourceModel\Selection\Collection\FilterApplier::class
            ):null;

            $serializer = class_exists('Magento\Framework\Serialize\Serializer\Json')?
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\Serializer\Json::class
            ):null;

            parent::__construct(
                $catalogProductOption,
                $eavConfig,
                $catalogProductType,
                $eventManager,
                $fileStorageDb,
                $filesystem,
                $coreRegistry,
                $logger,
                $productRepository,
                $catalogProduct,
                $catalogData,
                $bundleModelSelection,
                $bundleFactory,
                $bundleCollection,
                $config,
                $bundleSelection,
                $bundleOption,
                $storeManager,
                $priceCurrency,
                $stockRegistry,
                $stockState,
                $serializer,
                $metadataPool,
                $selectionCollectionFilterApplier  
            );

        } 
    }


    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then prepare of bundle selections options.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $result = \Magento\Catalog\Model\Product\Type\AbstractType::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        $selections = array();
        //$product = $this->getProduct($product);
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        $skipSaleableCheck = $this->catalogProductHelper->getSkipSaleableCheck();
        $_appendAllSelections = (bool) $product->getSkipCheckRequiredOption() || $skipSaleableCheck;

        $options = $buyRequest->getBundleOption();
        if (is_array($options)) {
            $options = array_filter($options, 'intval');
            $qtys = $buyRequest->getBundleOptionQty();
            foreach ($options as $_optionId => $_selections) {
                if (empty($_selections)) {
                    unset($options[$_optionId]);
                }
            }

            $optionIds = array_keys($options);

            if (empty($optionIds) && $isStrictProcessMode) {
                return __('Please select options for product.');
            }

            $productOptionIds = array();

            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection = $this->getOptionsCollection($product);
            foreach ($optionsCollection->getItems() as $option) {
                $productOptionIds[] = $option->getId();
                if (!$product->getSkipCheckRequiredOption() && $isStrictProcessMode) {
                    if ($option->getRequired() && !isset($options[$option->getId()])) {
                        return __('Required options are not selected.');
                    }
                }
            }
            $selectionIds = array();

            foreach ($options as $optionId => $selectionId) {
                if (in_array($optionId, $productOptionIds)) {
                    if (!is_array($selectionId)) {
                        if ($selectionId != '') {
                            $selectionIds[] = (int) $selectionId;
                        }
                    } else {
                        foreach ($selectionId as $id) {
                            if ($id != '') {
                                $selectionIds[] = (int) $id;
                            }
                        }
                    }
                }
            }

            $optionIds = array_keys($options);

            // If product has not been configured yet then $selections array should be empty
            if (!empty($selectionIds)) {
                $selections = $this->getSelectionsByIds($selectionIds, $product);
                // Check if added selections are still on sale
                foreach ($selections->getItems() as $key => $selection) {

                    if (!$selection->isSalable() && !$skipSaleableCheck) {
                        $_option = $optionsCollection->getItemById($selection->getOptionId());

                        if (is_array($options[$_option->getId()]) && count($options[$_option->getId()]) > 1) {
                            $moreSelections = true;
                        } else {
                            $moreSelections = false;
                        }
                        if ($_option->getRequired() && (!$_option->isMultiSelection() || ($_option->isMultiSelection() && !$moreSelections))
                        ) {
                            return __('Selected required options are not available.');
                        }
                    }
                }

                $optionsCollection->appendSelections($selections, false, $_appendAllSelections);

                $selections = $selections->getItems();
            } else {
                $selections = array();
            }
        } else {
            $product->setOptionsValidationFail(true);
            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);

            $optionIds = $product->getTypeInstance(true)->getOptionsIds($product);
            $selectionIds = array();

            $selectionCollection = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                $optionIds, $product
            );

            $options = $optionCollection->appendSelections($selectionCollection, false, $_appendAllSelections);

            foreach ($options as $option) {
                if ($option->getRequired() && count($option->getSelections()) == 1) {
                    $selections = array_merge($selections, $option->getSelections());
                } else {
                    $selections = array();
                    break;
                }
            }
        }
        if (count($selections) > 0 || !$isStrictProcessMode) {
            $uniqueKey = array($product->getId());
            $selectionIds = array();

            // Shuffle selection array by option position
            usort($selections, array($this, 'shakeSelections'));

            foreach ($selections as $selection) {
                if ($selection->getSelectionCanChangeQty() && isset($qtys[$selection->getOptionId()])) {
                    $qty = (float) $qtys[$selection->getOptionId()] > 0 ? $qtys[$selection->getOptionId()] : 1;
                } else {
                    $qty = (float) $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                }
                $qty = (float) $qty;

                $product->addCustomOption('selection_qty_' . $selection->getSelectionId(), $qty, $selection);
                $selection->addCustomOption('selection_id', $selection->getSelectionId());

                $beforeQty = 0;
                $customOption = $product->getCustomOption('product_qty_' . $selection->getId());
                if ($customOption) {
                    $beforeQty = (float) $customOption->getValue();
                }
                $product->addCustomOption('product_qty_' . $selection->getId(), $qty + $beforeQty, $selection);

                /*
                 * Create extra attributes that will be converted to product options in order item
                 * for selection (not for all bundle)
                 */
                $price = $product->getPriceModel()->getSelectionFinalTotalPrice($product, $selection, 0, $qty);
                $attributes = array(
                    'price' => $this->priceCurrency->convert($price),
                    'qty' => $qty,
                    'option_label' => $selection->getTitle(),
                    'option_id' => $selection->getId()
                );



                $_result = $selection->getTypeInstance(true)->prepareForCart($buyRequest, $selection);
                if (is_string($_result) && !is_array($_result)) {
                    return $_result;
                }

                if (!isset($_result[0])) {
                    return __('Cannot add item to the shopping cart.');
                }

                $result[] = $_result[0]->setParentProductId($product->getId())
                    ->addCustomOption('bundle_option_ids',  $this->serializer->serialize(array_map('intval', $optionIds)))
                    ->addCustomOption('bundle_selection_attributes',  $this->serializer->serialize($attributes));

                if ($isStrictProcessMode) {
                    $_result[0]->setCartQty($qty);
                }

                $selectionIds[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $qty;
            }

            // "unique" key for bundle selection and add it to selections and bundle for selections
            $uniqueKey = implode('_', $uniqueKey);
            foreach ($result as $item) {
                $item->addCustomOption('bundle_identity', $uniqueKey);
            }
            $product->addCustomOption('bundle_option_ids',  $this->serializer->serialize(array_map('intval', $optionIds)));
            $product->addCustomOption('bundle_selection_ids',  $this->serializer->serialize($selectionIds));


            return $result;
        }

        return $this->getSpecifyOptionMessage();
    }

    /**
     * Sort selections method for usort function
     * Sort selections by option position, selection position and selection id
     *
     * @param  \Magento\Catalog\Model\Product $a
     * @param  \Magento\Catalog\Model\Product $b
     * @return int
     */
    public function shakeSelections($a, $b)
    {
        $aPosition = array(
            $a->getPosition(),
            $a->getOptionId(),
            $a->getPosition(),
            $a->getSelectionId()
        );
        $bPosition = array(
            $b->getPosition(),
            $b->getOptionId(),
            $b->getPosition(),
            $b->getSelectionId()
        );
        if ($aPosition == $bPosition) {
            return 0;
        } else {
            return $aPosition < $bPosition ? -1 : 1;
        }
    }
    
    /**
     * Prepare Quote Item Quantity
     *
     * @param mixed $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareQuoteItemQty($qty, $product)
    {
        return $qty;
    }

}
