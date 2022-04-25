<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Category\Indexer;

use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Epicor\Elasticsearch\Model\Category\Indexer\Fulltext\Action\Full;
use Epicor\Elasticsearch\Model\Category\Indexer\IndexerHandlerFactory;
use Magento\Elasticsearch\Model\Config;

/**
 * Categories fulltext indexer
 * @package Epicor\Elasticsearch\Model\Category\Indexer
 */
class Fulltext implements ActionInterface, MviewActionInterface
{
    /**
     * Category Indexer Id
     */
    const INDEXER_ID = 'ecc_category_fulltext';

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandler;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var Full
     */
    private $fullAction;

    /**
     * @var Config
     */
    private $config;

    /**
     * Fulltext constructor.
     * @param IndexerHandlerFactory $indexerHandler
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     * @param Full $fullAction
     * @param Config $config
     */
    public function __construct(
        IndexerHandlerFactory $indexerHandler,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        Full $fullAction,
        Config $config
    ) {
        $this->indexerHandler = $indexerHandler;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->fullAction = $fullAction;
        $this->config = $config;
    }

    /**
     * Execute materialization on ids entities
     * @param int[] $ids The ids
     * @return void
     */
    public function execute($ids)
    {
        if ($this->isElasticSearchEnabled()) {
            $storeIds = array_keys($this->storeManager->getStores());
            foreach ($storeIds as $storeId) {
                $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
                $this->indexerHandler->create()->deleteIndex([$dimension], new \ArrayObject($ids));
                $this->indexerHandler->create()->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $ids));
            }
        }
    }

    /**
     * Execute full indexation
     * @return void
     */
    public function executeFull()
    {
        if ($this->isElasticSearchEnabled()) {
            $storeIds = array_keys($this->storeManager->getStores());
            foreach ($storeIds as $storeId) {
                $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
                $this->indexerHandler->create()->cleanIndex([$dimension]);
                $this->indexerHandler->create()->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function executeList(array $pageIds)
    {
        $this->execute($pageIds);
    }

    /**
     * {@inheritDoc}
     */
    public function executeRow($pageId)
    {
        $this->execute([$pageId]);
    }

    /**
     * @return bool
     */
    private function isElasticSearchEnabled()
    {
        return $this->config->isElasticsearchEnabled();
    }
}
