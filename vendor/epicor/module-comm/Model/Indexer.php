<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Epicor\SalesRep\Model\Pricing\Rule\Product\Indexer\ProductProcessor;
use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;

class Indexer
{

    const INDEXER_TYPE_PRODUCT_PRICE = 1;
    const DATA_TYPE_PRODUCT = 'catalog/product';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Indexer\Model\Indexer
     */
    protected $indexerIndexer;

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    protected $configInterface;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $catalogInventoryApiStockRegistryInterface;

    /**
     * @var \Magento\Framework\Indexer\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_flatstate;

    /**
     * @var \Magento\Indexer\Model\Indexer\State
     */
    protected $indexerState;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Indexer\Model\Indexer $indexerIndexer,
        \Magento\Framework\Indexer\ConfigInterface $config,
        \Magento\Framework\Indexer\ActionFactory $actionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockRegistryInterface $catalogInventoryApiStockRegistryInterface,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Indexer\Model\Indexer\State $indexerState,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $flatstate
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->registry = $registry;
        $this->indexerIndexer = $indexerIndexer;
        $this->configInterface = $config;
        $this->actionFactory = $actionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->catalogInventoryApiStockRegistryInterface = $catalogInventoryApiStockRegistryInterface;
        $this->indexerRegistry = $indexerRegistry;
        $this->_flatstate = $flatstate;
        $this->indexerState = $indexerState;

    }


    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $force
     */
    public function indexProduct($product)
    {
        $this->runProductIndex($product->getId());
    }

    public function indexProductById($productId)
    {
        $this->runProductIndex($productId);
    }

    public function runProductIndex($productId)
    {
        if (!is_array($productId)) {
            $productId = [$productId];
        }
        $indexerList[] = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;
        $indexerList[] = Category::INDEXER_ID;
        $indexerList[] = \Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID;
        $indexerList[] = \Magento\Catalog\Model\Indexer\Product\Eav\Processor::INDEXER_ID;
        $indexerList[] = ProductRuleProcessor::INDEXER_ID;
        $indexerList[] = Processor::INDEXER_ID;
        $indexerList[] = ProductProcessor::INDEXER_ID;

        $this->registry->register('epicor_comm_indexer_running', true);

        $indexers = $this->getConfig()->getIndexers();
        if (!empty($productId)) {
            foreach ($indexerList as $indexerName) {
                $indexer = $this->indexerRegistry->get($indexerName);
                if (!$indexer->isScheduled()) {
                    if (isset($indexers[$indexerName])) {
                        $indexer = $indexers[$indexerName];
                        $class = $this->actionFactory->create($indexer['action_class']);
                        foreach ($productId as $pid) {
                            $class->executeRow($pid);
                        }
                    }
                } else {
                    if ($indexerName == \Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID) {
                        $this->indexerState->loadByIndexer($indexerName);
                        $this->indexerState->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID);
                        $this->indexerState->save();
                    }
                }
            }
        }


//        if (!empty($productId)) {
//            foreach ($indexerList as $indexerName) {
//                $indexer = $this->indexerRegistry->get($indexerName);
//                if (!$indexer->isScheduled()) {
//                    $indexer->reindexList($productId);
//                }
//            }
//        }

//        if (!empty($productId) && $this->_flatstate->isFlatEnabled()) {
//            $indexer = $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Flat\Processor::INDEXER_ID);
//            if (!$indexer->isScheduled()) {
//                foreach ($productId as $productI) {
//                    $indexer->reindexRow($productI);
//                }
//            }
//        }

        if ($this->_flatstate->isFlatEnabled()) {
            $indexer = $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Flat\Processor::INDEXER_ID);
            if (!$indexer->isScheduled()) {
                $indexer->invalidate();
            }
        }

//        $productAttributes = $this->registry->registry('index_product_attributes');
//        if (!empty($productAttributes)) {
//            foreach ($productAttributes as $key => $value) {
//                $this->indexEntity('catalog_eav_attribute', $key);
//            }
//            $this->registry->unregister('index_product_attributes');
//        }
        $this->registry->unregister('epicor_comm_indexer_running');
    }

    public function indexEntity($entity, $id)
    {
        $indexers = $this->getConfig()->getIndexers();
        if (isset($indexers[$entity])) {
            $indexer = $indexers[$entity];
            $class = $this->actionFactory->create($indexer['action_class']);
            $class->executeRow($id);
        }
    }

    private function getConfig()
    {
        return $this->configInterface;
    }

}
