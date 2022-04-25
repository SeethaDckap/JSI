<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\Pricing\Rule\Product\Indexer;

use Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\Product\Indexer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\CacheContext;
use Psr\Log\LoggerInterface;

class ProductIndexer  implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var Indexer
     */
    protected $salesRepResourcePricingRuleProductIndexer;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Indexer $salesRepResourcePricingRuleProductIndexer
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Indexer $salesRepResourcePricingRuleProductIndexer,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig = null
    ) {
        $this->salesRepResourcePricingRuleProductIndexer = $salesRepResourcePricingRuleProductIndexer;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function execute($ids)
    {
        $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        if (!$this->scopeConfig->getValue('epicor_salesrep/general/enabled')) {
            return;
        }
        $resourceModel = $this->salesRepResourcePricingRuleProductIndexer;
        /* @var $resourceModel Indexer */
        $resourceModel->beginTransaction();
        try {
            $resourceModel->invalidateIndex();
            $resourceModel->reIndex();
            $resourceModel->deleteInvalid();
            $resourceModel->commit();
        } catch (\Exception $e) {
            $resourceModel->rollBack();
            $this->logger->critical($e);
            $this->logger->log(null, $e->getMessage());
        }
    
        $this->getCacheContext()->registerTags(
            [
                \Magento\Catalog\Model\Category::CACHE_TAG,
                \Magento\Catalog\Model\Product::CACHE_TAG
            ]
        );
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function executeList(array $ids)
    {
       // $this->_productStockIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     */
    public function executeRow($ruleId)
    {
        if ($ruleId) {
            $resourceModel = $this->salesRepResourcePricingRuleProductIndexer;
            /* @var $resourceModel Indexer */
            $resourceModel->beginTransaction();
            try {
                $resourceModel->invalidateIndex($ruleId);
                $resourceModel->reIndex($ruleId);
                $resourceModel->deleteInvalid($ruleId);
                $resourceModel->commit();
            } catch (\Exception $e) {
                $resourceModel->rollBack();
                $this->logger->critical($e);
                $this->logger->log(200,
                                   'Sales Rep Indexer - reindexRule(' . $ruleId . ')');
                $this->logger->log(200,
                                   $e->getMessage());
            }
        }
    }

    /**
     * Get cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated
     */
    protected function getCacheContext()
    {
        if (!($this->cacheContext instanceof CacheContext)) {
            return ObjectManager::getInstance()->get(CacheContext::class);
        } else {
            return $this->cacheContext;
        }
    }
}
