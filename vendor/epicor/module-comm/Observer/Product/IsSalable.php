<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Product;

class IsSalable extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $catalogInventoryApiStockRegistryInterface;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $catalogInventoryApiStockRegistryInterface,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $catalogInventoryStockItemFactory)
    {
        $this->catalogInventoryApiStockRegistryInterface=$catalogInventoryApiStockRegistryInterface;

        parent::__construct($catalogInventoryStockItemFactory);
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salable = $observer->getEvent()->getSalable();
        $product = $salable->getProduct();
        /* @var $product \Epicor\Comm\Model\Product */
        if ($product) {
            //M1 > M2 Translation Begin (Rule 23)
            //$stockItemData = $product->getStockItem();
            //$stockItemData = $this->catalogInventoryApiStockRegistryInterface->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
            //M1 > M2 Translation End

            /* @var $stockItemData \Epicor\Comm\Model\Cataloginventory\Stock\Item */

        $stockItemData = $product->getStockItem();
        /* @var $stockItemData Epicor_Comm_Model_Cataloginventory_Stock_Item */
        //if ($stockItemData !== NULL) {
        if ($stockItemData instanceof \Magento\CatalogInventory\Model\Stock\Item) {
            if (!$stockItemData->getProductId()) {
                $stockItem = $this->catalogInventoryStockItemFactory->create();
                /* @var $stockItem \Epicor\Comm\Model\Cataloginventory\Stock\Item */
                //M1 > M2 Translation Begin (Rule 6)
                //$stockItem->loadByProduct($product->getId());
                $stockItem->getResource()->loadByProductId($stockItem, $product->getId(), $stockItem->getStockId());
                //M1 > M2 Translation End
                $stockItem->addData($stockItemData->getData());
                $product->setStockItem($stockItem);
            }

            //M1 > M2 Translation Begin (Rule 23)
            //if ($product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED && $product->getStockItem()->getBackorders()) {
            if ($product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED && $stockItemData->getBackorders()) {
                //M1 > M2 Translation End
                $salable->setIsSalable(true);
            }
        }
        return $this;
    }

}

}