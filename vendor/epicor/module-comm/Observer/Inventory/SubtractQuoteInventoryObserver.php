<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\Comm\Observer\Inventory;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Catalog inventory module observer
 */
class SubtractQuoteInventoryObserver implements ObserverInterface
{
    /**
     * @var StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var ProductQty
     */
    protected $productQty;

    /**
     * @var \Magento\CatalogInventory\Observer\ItemsForReindex
     */
    protected $itemsForReindex;

    /**
     * SubtractQuoteInventoryObserver constructor.
     * @param StockManagementInterface $stockManagement
     * @param ProductQty $productQty
     * @param ItemsForReindex $itemsForReindex
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Observer\ProductQty $productQty,
        \Magento\CatalogInventory\Observer\ItemsForReindex $itemsForReindex
    ) {
        $this->stockManagement = $stockManagement;
        $this->productQty = $productQty;
        $this->itemsForReindex = $itemsForReindex;
    }

    /**
     * Subtract quote items qtys from stock items related with quote items products.
     *
     * Used before order placing to make order save/place transaction smaller
     * Also called after every successful order placement to ensure subtraction of inventory
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        // Maybe we've already processed this quote in some event during order placement
        // e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
        $quote->setInventoryProcessed(true);

        if ($quote->getInventoryProcessed()) {
            return $this;
        }

        $items = $this->productQty->getProductQty($quote->getAllItems());

        /**
         * Remember items
         */
        $itemsForReindex = $this->stockManagement->registerProductsSale(
            $items,
            $quote->getStore()->getWebsiteId()
        );
        if (count($itemsForReindex)) {
            $this->itemsForReindex->setItems($itemsForReindex);
        }
        $quote->setInventoryProcessed(true);
        return $this;
    }
}