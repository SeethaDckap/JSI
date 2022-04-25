<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Product;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $catalogInventoryStockItemFactory;

    public function __construct(
        \Magento\CatalogInventory\Model\Stock\ItemFactory $catalogInventoryStockItemFactory
    ) {
        $this->catalogInventoryStockItemFactory = $catalogInventoryStockItemFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }


}

