<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\SalesRep\Plugin\Indexer;

abstract class AbstractPlugin
{

    /** @var \Magento\Framework\Indexer\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    )
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int[] $productIds
     * @return void
     */
    protected function reindex()
    {
        $indexer = $this->indexerRegistry->get('salesrep_pricing_rule_product');
        if (!$indexer->isScheduled()) {
            $indexer->reindexAll();
        }
    }

}
