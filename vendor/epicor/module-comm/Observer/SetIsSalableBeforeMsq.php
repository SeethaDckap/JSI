<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

use http\Exception\InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Event\Observer;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface  as Logger;
use Epicor\Comm\Model\Product;

/**
 * Set the is_salable product value before MSQ if required
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class SetIsSalableBeforeMsq implements ObserverInterface
{
    const CONFIG_PATH_PRODUCTS_ALWAYS_IN_STOCK
        = 'epicor_comm_enabled_messages/msq_request/products_always_in_stock';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var bool
     */
    private $alwaysInStock;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * SetIsSalableBeforeMsq constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StockRegistry $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StockRegistry $stockRegistry,
        StoreManagerInterface $storeManager,
        Logger $logger
    ){
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->alwaysInStock === null) {
            $this->alwaysInStock = $this->scopeConfig
                ->getValue(self::CONFIG_PATH_PRODUCTS_ALWAYS_IN_STOCK, ScopeInterface::SCOPE_STORE);
        }

        $product = $observer->getData('product');

        $qty = $this->getStockQty($product);

        if (!$this->alwaysInStock && $qty <= 0) {
            $product->setData('is_salable', false);
        }
    }

    private function getStockQty($product)
    {
        $qty = 0;
        try {
            if (!$product instanceof Product) {
                throw new InvalidArgumentException('Object needs to by of type Epicor\Comm\Model\Product');
            }
            $scopeId = $this->storeManager->getStore()->getId();
            $stockItem = $this->stockRegistry->getStockItem($product->getId(), $scopeId);
            $qty = $stockItem->getQty();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $qty;
    }
}
