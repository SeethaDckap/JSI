<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\ResourceModel\Eav\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;
use Epicor\Elasticsearch\Model\ResourceModel\Indexer\AbstractIndexer;

/**
 * This class provides a lot of util methods used by Eav indexer related resource models.
 */
class Indexer extends AbstractIndexer
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * Indexer constructor.
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool)
    {
        parent::__construct($resource, $storeManager);
        $this->metadataPool = $metadataPool;
    }

    /**
     * Retrieve store root category id.
     * @param \Magento\Store\Api\Data\StoreInterface|int|string $store Store id.
     * @return integer
     */
    protected function getRootCategoryId($store)
    {
        if (is_numeric($store) || is_string($store)) {
            $store = $this->getStore($store);
        }
        $storeGroupId = $store->getStoreGroupId();
        return $this->storeManager->getGroup($storeGroupId)->getRootCategoryId();
    }

    /**
     * Retrieve Metadata for an entity
     * @param string $entityType The entity
     * @return EntityMetadataInterface
     */
    protected function getEntityMetaData($entityType)
    {
        return $this->metadataPool->getMetadata($entityType);
    }
}
