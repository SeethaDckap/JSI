<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer\Inventory;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ReindexQuoteInventoryObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $stockIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $priceIndexer;

    /**
     * @var \Magento\CatalogInventory\Observer\ItemsForReindex
     */
    protected $itemsForReindex;

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param ItemsForReindex $itemsForReindex
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magento\CatalogInventory\Observer\ItemsForReindex $itemsForReindex
    ) {
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
        $this->itemsForReindex = $itemsForReindex;
    }

    /**
     * Refresh stock index for specific stock items after successful order placement
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();

        $quote->setInventoryProcessed(true);

        if ($quote->getInventoryProcessed()) {
            return $this;
        }

        $productIds = [];
        foreach ($quote->getAllItems() as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }

        if ($productIds) {
            $this->stockIndexerProcessor->reindexList($productIds);
        }

        // Reindex previously remembered items
        $productIds = [];
        foreach ($this->itemsForReindex->getItems() as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }

        if (!empty($productIds)) {
            $this->priceIndexer->reindexList($productIds);
        }

        $this->itemsForReindex->clear();
        // Clear list of remembered items - we don't need it anymore
    }
}
