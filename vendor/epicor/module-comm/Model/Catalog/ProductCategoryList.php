<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Catalog;

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Framework\App\ObjectManager;


/**
 * Provides info about product categories.
 */
class ProductCategoryList
{
    /**
     * @var array
     */
    private $categoryIdList = [];

    /**
     * @var ResourceModel\Product
     */
    private $productResource;

    /**
     * @var ResourceModel\Category
     */
    private $category;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;

    /**
     * @param ResourceModel\Product $productResource
     * @param ResourceModel\Category $category
     * @param StoreManagerInterface $storeManager
     * @param TableMaintainer|null $tableMaintainer
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Model\ResourceModel\Category $category,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null
    ) {
        $this->productResource = $productResource;
        $this->category = $category;
        $this->productMetadata = $productMetadata;
        if($this->productMetadata->getVersion() < '2.2.5') {
            $this->tableMaintainer = null;
        } else {
            $this->tableMaintainer = class_exists('\Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer')?
                \Magento\Framework\App\ObjectManager::getInstance()->get(
                    \Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer::class
                ):null;
        }
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(\Magento\Store\Model\StoreManagerInterface::class);
    }

    /**
     * Retrieve category id list where product is present.
     *
     * @param int $productId
     * @return array
     */
    public function getCategoryIds($productId)
    {
        if($this->productMetadata->getVersion() < '2.2.5') {
            if (!isset($this->categoryIdList[$productId])) {
                $unionSelect = new UnionExpression(
                    [
                        $this->getCategorySelect($productId, $this->category->getCategoryProductTable()),
                        $this->getCategorySelect(
                            $productId,
                            $this->productResource->getTable(AbstractAction::MAIN_INDEX_TABLE)
                        )
                    ],
                    Select::SQL_UNION_ALL
                );
                $this->categoryIdList[$productId] = $this->productResource->getConnection()->fetchCol($unionSelect);
            }
        } else {
            if (!isset($this->categoryIdList[$productId])) {
                $unionTables[] = $this->getCategorySelect($productId, $this->category->getCategoryProductTable());
                foreach ($this->storeManager->getStores() as $store) {
                    $unionTables[] = $this->getCategorySelect(
                        $productId,
                        $this->tableMaintainer->getMainTable($store->getId())
                    );
                }
                $unionSelect = new UnionExpression(
                    $unionTables,
                    Select::SQL_UNION_ALL
                );

                $this->categoryIdList[$productId] = $this->productResource->getConnection()->fetchCol($unionSelect);
            }
        }
        return $this->categoryIdList[$productId];
    }

    /**
     * Returns DB select.
     *
     * @param int $productId
     * @param string $tableName
     * @return Select
     */
    public function getCategorySelect($productId, $tableName)
    {
        return $this->productResource->getConnection()->select()->from(
            $tableName,
            ['category_id']
        )->where(
            'product_id = ?',
            $productId
        );
    }
}