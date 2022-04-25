<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\ResourceModel\Cmspage\Indexer\Fulltext\Action;

use Epicor\Elasticsearch\Model\ResourceModel\Eav\Indexer\Indexer;
use Magento\Cms\Api\Data\PageInterface;

/**
 * Elasticsearch CMS Page full indexer resource model.
 */
class Full extends Indexer
{

    /**
     * @var string[]
     */
    private $indexAttributes = [
        'page_id',
        'title',
        'is_active',
        'identifier',
        'content_heading',
        'content',
        'creation_time',
        'update_time'
    ];
    /**
     * Load a bulk of CMS Page data.
     * @param int     $storeId     Store id.
     * @param string  $cmsPageIds  CMS Page ids filter.
     * @param integer $fromId      Load CMS Page with id greater than.
     * @param integer $limit       Number of CMS Page to get loaded.
     *
     * @return array
     */
    public function getSearchableCmsPages($storeId, $cmsPageIds = null, $fromId = 0, $limit = 100): array
    {
        $select = $this->getConnection()->select()
            ->from(['p' => $this->getTable('cms_page')]);

        $indexAttributes = $this->getIndexAttributes();
        $select->reset('columns')
            ->columns($indexAttributes);
        $this->addIsVisibleInStoreFilter($select, $storeId);

        if ($cmsPageIds !== null) {
            $select->where('p.page_id IN (?)', $cmsPageIds);
        }
        $select->where('p.page_id > ?', $fromId)
            ->where('p.is_active = ?', true)
            ->limit($limit)
            ->order('p.page_id');
        return $this->connection->fetchAll($select);
    }

    /**
     * Filter the select to append only CMS pages of current store.
     * @param \Zend_Db_Select $select
     * @param int $storeId
     * @return Full
     */
    private function addIsVisibleInStoreFilter($select, $storeId): Full
    {
        $linkField = $this->metadataPool->getMetadata(PageInterface::class)->getLinkField();

        $select->join(
            ['ps' => $this->getTable('cms_page_store')],
            "p.$linkField = ps.$linkField",
            ['store_id']
        );
        $select->where('ps.store_id IN (?)', [0, $storeId]);

        return $this;
    }

    /**
     * Returns fields that needs to be reindexed
     * @return array
     */
    private function getIndexAttributes()
    {
        return $this->indexAttributes;
    }
}
