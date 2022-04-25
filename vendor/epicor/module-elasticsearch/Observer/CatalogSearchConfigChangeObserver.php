<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elasticsearch\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Indexer\IndexerRegistry;

class CatalogSearchConfigChangeObserver implements ObserverInterface
{
    /**
     * XML Path for Catalog Search Engine
     */
    const XML_PATH_CATALOG_SEARCH_ENGINE = 'catalog/search/engine';

    /**
     * XML Path for Auto Hide Categories
     */
    const XML_PATH_CATALOG_AUTO_HIDE = 'epicor_common/catalog_navigation/auto_hide';

    /**
     * ECC Category Indexer Id
     */
    const ECC_CATEGORY_INDEXER = 'ecc_category_fulltext';

    /**
     * ECC CMS Pages Indexer Id
     */
    const ECC_CMS_PAGE_INDEXER = 'ecc_cms_pages_fulltext';

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var string[]
     */
    private $indexers = [
        self::ECC_CATEGORY_INDEXER,
        self::ECC_CMS_PAGE_INDEXER
    ];

    /**
     * CatalogSearchConfigChangeObserver constructor.
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }


    /**
     * Invalidate the indexer when catalog configs are changed
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $changedPaths = (array) $observer->getEvent()->getChangedPaths();
        $this->checkAndReindex($changedPaths);
    }

    /**
     * Check if index needs to be invalidated or not
     * @param $changedPaths
     */
    private function checkAndReindex($changedPaths)
    {
        $match = preg_grep('/index_prefix+$/', $changedPaths);
        if (empty($match) === false
            || in_array(self::XML_PATH_CATALOG_SEARCH_ENGINE, $changedPaths, true)
        ) {
            foreach ($this->indexers as $indexId) {
                $this->reindex($indexId);
            }
        } else if (in_array(self::XML_PATH_CATALOG_AUTO_HIDE, $changedPaths, true)) {
            $this->reindex(self::ECC_CATEGORY_INDEXER);
        }
    }

    /**
     * Reindexes the given indexer
     * @param $indexId
     */
    private function reindex($indexId)
    {
        $this->indexerRegistry->get($indexId)->invalidate();
    }
}

