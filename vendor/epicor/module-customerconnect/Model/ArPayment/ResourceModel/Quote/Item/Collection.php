<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote\Item;

use \Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Quote item resource collection
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    /**
     * Collection quote instance
     *
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote
     */
    protected $_quote;

    /**
     * Product Ids array
     *
     * @var int[]
     */
    protected $_productIds = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote\Config
     */
    protected $_quoteConfig;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Epicor\Customerconnect\Model\ArPayment\Quote\Config $quoteConfig
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Epicor\Customerconnect\Model\ArPayment\Quote\Config $quoteConfig,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_quoteConfig = $quoteConfig;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Epicor\Customerconnect\Model\ArPayment\Quote\Item::class, \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote\Item::class);
    }

    /**
     * Retrieve store Id (From Quote)
     *
     * @return int
     */
    public function getStoreId()
    {
        return (int)$this->_productCollectionFactory->create()->getStoreId();
    }

    /**
     * Set Quote object to Collection
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->_quote = $quote;
        $quoteId = $quote->getId();
        if ($quoteId) {
            $this->addFieldToFilter('quote_id', $quote->getId());
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * Reset the collection and inner join it to quotes table
     * Optionally can select items with specified product id only
     *
     * @param string $quotesTableName
     * @param int $productId
     * @return $this
     */
    public function resetJoinQuotes($quotesTableName, $productId = null)
    {
        $this->getSelect()->reset()->from(
            ['qi' => $this->getResource()->getMainTable()],
            ['item_id', 'qty', 'quote_id']
        )->joinInner(
            ['q' => $quotesTableName],
            'qi.quote_id = q.entity_id',
            ['store_id', 'items_qty', 'items_count']
        );
        if ($productId) {
            $this->getSelect()->where('qi.product_id = ?', (int)$productId);
        }
        return $this;
    }

    /**
     * After load processing
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        return $this;
    }



    /**
     * Add products to items and item options
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _assignProducts()
    {
        \Magento\Framework\Profiler::start('QUOTE:' . __METHOD__, ['group' => 'QUOTE', 'method' => __METHOD__]);
        $productCollection = $this->_productCollectionFactory->create()->setStoreId(
            $this->getStoreId()
        )->addIdFilter(
            $this->_productIds
        )->addAttributeToSelect(
            $this->_quoteConfig->getProductAttributes()
        );
        $this->skipStockStatusFilter($productCollection);
        $productCollection->addOptionsToResult()->addStoreFilter()->addUrlRewrite();
        $this->addTierPriceData($productCollection);

        $this->_eventManager->dispatch(
            'ar_prepare_catalog_product_collection_prices',
            ['collection' => $productCollection, 'store_id' => $this->getStoreId()]
        );
        $this->_eventManager->dispatch(
            'ar_sales_quote_item_collection_products_after_load',
            ['collection' => $productCollection]
        );

        $recollectQuote = false;
        foreach ($this as $item) {
            $product = $productCollection->getItemById($item->getProductId());
            if ($product) {
                $product->setCustomOptions([]);
                $qtyOptions = [];
                $optionProductIds = [];
                foreach ($item->getOptions() as $option) {
                    /**
                     * Call type-specific logic for product associated with quote item
                     */
                    $product->getTypeInstance()->assignProductToOption(
                        $productCollection->getItemById($option->getProductId()),
                        $option,
                        $product
                    );

                    if (is_object($option->getProduct()) && $option->getProduct()->getId() != $product->getId()) {
                        $optionProductIds[$option->getProduct()->getId()] = $option->getProduct()->getId();
                    }
                }

                if ($optionProductIds) {
                    foreach ($optionProductIds as $optionProductId) {
                        $qtyOption = $item->getOptionByCode('product_qty_' . $optionProductId);
                        if ($qtyOption) {
                            $qtyOptions[$optionProductId] = $qtyOption;
                        }
                    }
                }

                $item->setQtyOptions($qtyOptions)->setProduct($product);
            } else {
                $item->isDeleted(true);
                $recollectQuote = true;
            }
            $item->checkData();
        }

        if ($recollectQuote && $this->_quote) {
            $this->_quote->collectTotals();
        }
        \Magento\Framework\Profiler::stop('QUOTE:' . __METHOD__);

        return $this;
    }

    /**
     * Prevents adding stock status filter to the collection of products.
     *
     * @param ProductCollection $productCollection
     * @return void
     *
     * @see \Magento\CatalogInventory\Helper\Stock::addIsInStockFilterToCollection
     */
    private function skipStockStatusFilter(ProductCollection $productCollection)
    {
        $productCollection->setFlag('has_stock_status_filter', true);
    }

    /**
     * Add tier prices to product collection.
     *
     * @param ProductCollection $productCollection
     * @return void
     */
    private function addTierPriceData(ProductCollection $productCollection)
    {
        if (empty($this->_quote)) {
            $productCollection->addTierPriceData();
        } else {
            $productCollection->addTierPriceDataByGroupId($this->_quote->getCustomerGroupId());
        }
    }

    /**
     * Find and remove quote items with non existing products
     *
     * @return void
     */
    private function removeItemsWithAbsentProducts()
    {
        $productCollection = $this->_productCollectionFactory->create()->addIdFilter($this->_productIds);
        $existingProductsIds = $productCollection->getAllIds();
        $absentProductsIds = array_diff($this->_productIds, $existingProductsIds);
        // Remove not existing products from items collection
        if (!empty($absentProductsIds)) {
            foreach ($absentProductsIds as $productIdToExclude) {
                /** @var \Epicor\Customerconnect\Model\ArPayment\Quote\Item $quoteItem */
                $quoteItem = $this->getItemByColumnValue('product_id', $productIdToExclude);
                $this->removeItemByKey($quoteItem->getId());
            }
        }
    }
}
