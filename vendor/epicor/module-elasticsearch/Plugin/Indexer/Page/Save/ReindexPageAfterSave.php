<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elasticsearch\Plugin\Indexer\Page\Save;

use Magento\Cms\Model\ResourceModel\Page;
use Magento\Framework\Indexer\IndexerRegistry;
use Epicor\Elasticsearch\Model\Cmspage\Indexer\Fulltext;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ReindexPageAfterSave
 * Plugin that proceed cms page reindex in ES after cms page save
 * @package Epicor\Elasticsearch\Plugin\Indexer\Page\Save
 */
class ReindexPageAfterSave
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * ReindexCategoryAfterSave constructor.
     *
     * @param IndexerRegistry $indexerRegistry The indexer registry
     */
    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex cms page's data into search engine after saving the cms page.
     * @param Page $subject
     * @param AbstractDb $result
     * @param AbstractModel $page
     * @return \Magento\Cms\Model\Page
     */
    public function afterSave(
        Page $subject,
        AbstractDb $result,
        AbstractModel $page
    )
    {
        $this->reindexCmsPage($page);
         return $result;
    }

    /**
     * Reindex cms page's data into search engine after deleting the cms page.
     * @param Page $subject
     * @param AbstractDb $result
     * @param AbstractModel $page
     * @return \Magento\Cms\Model\Page
     */
    public function afterDelete(
        Page $subject,
        AbstractDb $result,
        AbstractModel $page
    )
    {
        $this->reindexCmsPage($page);
        return $result;
    }

    /**
     * Reindexes the CMS Page indez
     * @param $page
     */
    private function reindexCmsPage($page)
    {
        $cmsPageIndexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        if ($cmsPageIndexer->isScheduled() === false) {
            $cmsPageIndexer->reindexRow($page->getId());
        }
        return;
    }
}
