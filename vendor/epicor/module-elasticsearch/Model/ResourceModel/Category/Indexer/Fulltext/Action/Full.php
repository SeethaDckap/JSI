<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\ResourceModel\Category\Indexer\Fulltext\Action;

use Epicor\Common\Helper\DataFactory;
use Epicor\Elasticsearch\Model\ResourceModel\Eav\Indexer\Indexer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Eav\Setup\EavSetup;

/**
 * Elasticsearch category full indexer resource model.
 */
class Full extends Indexer
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var EavSetup
     */
    private $eavSetupFactory;

    /**
     * @var DataFactory
     */
    private $commonHelper;

    /**
     * Full constructor.
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param EavSetup $eavSetupFactory
     * @param DataFactory $commonHelper
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        CategoryCollectionFactory $categoryCollectionFactory,
        EavSetup $eavSetupFactory,
        DataFactory $commonHelper
    ) {
        parent::__construct($resource, $storeManager, $metadataPool);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->commonHelper = $commonHelper;
    }

    /**
     * Load a bulk of category data.
     * @param int     $storeId     Store id.
     * @param string  $categoryIds Product ids filter.
     * @param integer $fromId      Load product with id greater than.
     * @param integer $limit       Number of product to get loaded.
     *
     * @return array
     */
    public function getSearchableCategories($storeId, $categoryIds = null, $fromId = 0, $limit = 100): array
    {
        $helper = $this->commonHelper->create();
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection
         */
        $categoryCollection = $this->categoryCollectionFactory->create()->setStoreId($storeId);
        $categoryCollection->addIsActiveFilter();
        $select = $categoryCollection->getSelect();
        $select->distinct(true);

        $categoryNameAttributeId = $this->eavSetupFactory->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'name');
        $joinConditionForName = "cv1_default.entity_id = e.entity_id AND cv1_default.attribute_id = $categoryNameAttributeId 
        AND cv1_default.store_id = 0";
        $select->join(
            ['cv1_default' => $this->getTable('catalog_category_entity_varchar')],
            $joinConditionForName,
            []
        );

        $leftJoinConditionForName = "cv1.entity_id = e.entity_id AND cv1.attribute_id = $categoryNameAttributeId 
        AND cv1.store_id = $storeId";
        $select->joinLeft(
        ['cv1' => $this->getTable('catalog_category_entity_varchar')],
            $leftJoinConditionForName,
        [
            "name" => "IF(cv1.value_id > 0, cv1.value, cv1_default.value)"
        ]
        );

        $categoryUrlKeyAttributeId = $this->eavSetupFactory->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'url_key');

        $joinConditionForUrlKey = "cv2_default.entity_id = e.entity_id AND cv2_default.attribute_id = $categoryUrlKeyAttributeId
        AND cv2_default.store_id = 0";
        $select->join(
            ['cv2_default' => $this->getTable('catalog_category_entity_varchar')],
            $joinConditionForUrlKey,
            []
        );

        $leftJoinConditionForUrlKey = "cv2.entity_id = e.entity_id AND cv2.attribute_id = $categoryUrlKeyAttributeId
        AND cv2.store_id = $storeId";
        $select->joinLeft(
            ['cv2' => $this->getTable('catalog_category_entity_varchar')],
            $leftJoinConditionForUrlKey,
            [
                "url_key" => "IF(cv2.value_id > 0, cv2.value, cv2_default.value)"
            ]
        );

        $categoryUrlPathAttributeId = $this->eavSetupFactory->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'url_path');

        $joinConditionForUrlPath = "cv3_default.entity_id = e.entity_id AND cv3_default.attribute_id = $categoryUrlPathAttributeId
        AND cv3_default.store_id = 0";
        $select->joinLeft(
            ['cv3_default' => $this->getTable('catalog_category_entity_varchar')],
            $joinConditionForUrlPath,
            []
        );

        $leftJoinConditionForUrlPath = "cv3.entity_id = e.entity_id AND cv3.attribute_id = $categoryUrlPathAttributeId
        AND cv3.store_id = $storeId";
        $select->joinLeft(
            ['cv3' => $this->getTable('catalog_category_entity_varchar')],
            $leftJoinConditionForUrlPath,
            [
                "url_path" => "IF(cv3.value_id > 0, cv3.value, cv3_default.value)"
            ]
        );

        $this->addIsVisibleInStoreFilter($select, $storeId);

        if ($categoryIds !== null) {
            $select->where('e.entity_id IN (?)', $categoryIds);
        }
        if ($helper->getAutohideCategories()) {
            $select->joinLeft(
                ['cp' => $categoryCollection->getProductTable()],
                "cp.category_id = e.entity_id",
                ""
            );
            $select->having("COUNT(cp.product_id) > 0");
            $select->group('e.entity_id');
        }
        $select->where('e.entity_id > ?', $fromId)
            ->limit($limit)
            ->order('e.entity_id');
        return $this->connection->fetchAll($select);
    }

    /**
     * Filter the select to append only categories that are childrens of the root category of current store.
     * @param $select
     * @param $storeId
     * @return Full
     */
    private function addIsVisibleInStoreFilter($select, $storeId): Full
    {
        $rootCategoryId = $this->getRootCategoryId($storeId);
        $select->where('e.path LIKE ?', "1/{$rootCategoryId}/%");
        return $this;
    }
}
