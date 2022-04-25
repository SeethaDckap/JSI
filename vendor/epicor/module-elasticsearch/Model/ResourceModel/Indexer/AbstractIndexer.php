<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\ResourceModel\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class provides a lot of util methods used by Eav indexer related resource models.
 */
class AbstractIndexer
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor.
     * @param ResourceConnection    $resource     Database adpater.
     * @param StoreManagerInterface $storeManager Store manager.
     */
    public function __construct(ResourceConnection $resource, StoreManagerInterface $storeManager)
    {
        $this->resource     = $resource;
        $this->connection   = $resource->getConnection();
        $this->storeManager = $storeManager;
    }


    /**
     * Get table name using the adapter.
     * @param string $tableName Table name.
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * Return database connection.
     * @return AdapterInterface
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get store by id.
     * @param integer $storeId Store id.
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStore($storeId)
    {
        return $this->storeManager->getStore($storeId);
    }
}
