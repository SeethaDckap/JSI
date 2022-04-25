<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Category\Indexer\Fulltext\Action;

use Epicor\Elasticsearch\Model\ResourceModel\Category\Indexer\Fulltext\Action\Full as ResourceModel;

/**
 * Class Full
 * @package Epicor\Elasticsearch\Model\Category\Indexer\Fulltext\Action
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
     * Get data for a list of categories in a store id.
     * If the category list ids is null, all categories data will be loaded.
     *
     * @param integer    $storeId     Store id.
     * @param array|null $categoryIds List of category ids.
     * @return \Traversable
     */
    public function rebuildStoreIndex($storeId, $categoryIds = null)
    {
        $lastCategoryId = 0;

        do {
            $categories = $this->getSearchableCategories($storeId, $categoryIds, $lastCategoryId);

            foreach ($categories as $categoryData) {
                $lastCategoryId = (int) $categoryData['entity_id'];
                yield $lastCategoryId => $categoryData;
            }
        } while (!empty($categories));
    }

    /**
     * Load a bulk of product data.
     * @param int     $storeId     Store id.
     * @param string  $categoryIds Category ids filter.
     * @param integer $fromId      Load product with id greater than.
     * @param integer $limit       Number of product to get loaded.
     * @return array
     */
    private function getSearchableCategories($storeId, $categoryIds = null, $fromId = 0, $limit = 100)
    {
        return $this->resourceModel->getSearchableCategories($storeId, $categoryIds, $fromId, $limit);
    }
}
