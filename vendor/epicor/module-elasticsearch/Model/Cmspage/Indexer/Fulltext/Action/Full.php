<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Cmspage\Indexer\Fulltext\Action;

use Epicor\Elasticsearch\Model\ResourceModel\Cmspage\Indexer\Fulltext\Action\Full as ResourceModel;

/**
 * Class Full
 * @package Epicor\Elasticsearch\Model\Cmspage\Indexer\Fulltext\Action
 */
class Full
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * Constructor.
     *
     * @param ResourceModel $resourceModel Indexer resource model.
     */
    public function __construct(ResourceModel $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * Get data for a list of CMS Pages in a store id.
     * If the CMS Pages list ids is null, all CMS Pages data will be loaded.
     *
     * @param integer    $storeId     Store id.
     * @param array|null $cmsPageIds List of CMS Page ids.
     * @return \Traversable
     */
    public function rebuildStoreIndex($storeId, $cmsPageIds = null)
    {
        $lastCmsPageId = 0;

        do {
            $cmsPages = $this->getSearchableCmsPages($storeId, $cmsPageIds, $lastCmsPageId);

            foreach ($cmsPages as $cmsPageData) {
                $lastCmsPageId = (int) $cmsPageData['page_id'];
                $cmsPageData['content'] = $this->escapeContent($cmsPageData['content']);
                yield $lastCmsPageId => $cmsPageData;
            }
        } while (!empty($cmsPages));
    }

    /**
     * Load a bulk of CMS pages data.
     * @param int     $storeId     Store id.
     * @param string  $cmsPageIds  CMS Page ids filter.
     * @param integer $fromId      Load CMS Page with id greater than.
     * @param integer $limit       Number of CMS Page to get loaded.
     * @return array
     */
    private function getSearchableCmsPages($storeId, $cmsPageIds = null, $fromId = 0, $limit = 100)
    {
        return $this->resourceModel->getSearchableCmsPages($storeId, $cmsPageIds, $fromId, $limit);
    }

    /**
     * Removes HTML tags from content
     * @param $content
     * @return string|string[]|null
     */
    private function escapeContent($content)
    {
        $content = strip_tags(trim(html_entity_decode($content), " \t\n\r\0\x0B\xC2\xA0"));
        $content = preg_replace("/\n/", " ", $content);
        $content = preg_replace("/\r/", "", $content);
        return $content;
    }
}
