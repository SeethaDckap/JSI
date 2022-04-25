<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin\Indexer\Category\Save;

use Epicor\Elasticsearch\Model\Category\Indexer\Fulltext;
use Magento\Catalog\Model\Category;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Plugin that proceed category reindex in ES after category reindexing
 */
class ReindexCategoryAfterSave
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * ReindexCategoryAfterSave constructor.
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex category's data after into search engine after reindexing the category.
     * @param Category $subject
     * @param callable $proceed
     */
    public function aroundReindex(
        Category $subject,
        callable $proceed
    ) {
        $proceed();
        if ($subject->getLevel() > 1) {
            $categoryIndexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
            if (!$categoryIndexer->isScheduled()) {
                $categoryIndexer->reindexRow($subject->getId());
            }
        }
        return;
    }
}
