<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Cmspage\Adapter;

use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Elasticsearch adapter
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Elasticsearch
{
    /**
     * Text flags for Elasticsearch bulk actions
     */
    const BULK_ACTION_INDEX = 'index';
    const BULK_ACTION_CREATE = 'create';
    const BULK_ACTION_DELETE = 'delete';
    const BULK_ACTION_UPDATE = 'update';

    /**
     * Buffer for total fields limit in mapping.
     */
    private const MAPPING_TOTAL_FIELDS_BUFFER_LIMIT = 1000;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var IndexNameResolver
     */
    private $indexNameResolver;

    /**
     * @var Config
     */
    private $clientConfig;

    /**
     * @var \Magento\Elasticsearch\Model\Client\Elasticsearch
     */
    private $client;

    /**
     * @var BuilderInterface
     */
    private $indexBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $preparedIndex = [];

    /**
     * Constructor for Elasticsearch adapter.
     * @param ConnectionManager $connectionManager
     * @param Config $clientConfig
     * @param BuilderInterface $indexBuilder
     * @param LoggerInterface $logger
     * @param IndexNameResolver $indexNameResolver
     * @param array $options
     * @throws LocalizedException
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Config $clientConfig,
        BuilderInterface $indexBuilder,
        LoggerInterface $logger,
        IndexNameResolver $indexNameResolver,
        $options = []
    ) {
        $this->connectionManager = $connectionManager;
        $this->clientConfig = $clientConfig;
        $this->indexBuilder = $indexBuilder;
        $this->logger = $logger;
        $this->indexNameResolver = $indexNameResolver;

        try {
            $this->client = $this->connectionManager->getConnection($options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __('The search failed because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * Retrieve Elasticsearch server status
     *
     * @return bool
     * @throws LocalizedException
     */
    public function ping()
    {
        try {
            $response = $this->client->ping();
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Could not ping search engine: %1', $e->getMessage())
            );
        }
        return $response;
    }

    /**
     * Create Elasticsearch documents by specified data
     *
     * @param array $documentData
     * @param int $storeId
     * @return array
     */
    public function prepareDocsPerStore(array $documentData, $storeId)
    {
        $documents = [];
        if (count($documentData)) {
            $documents = $this->batchDocumentDataMapper->map(
                $documentData,
                $storeId
            );
        }
        return $documents;
    }

    /**
     * Add prepared Elasticsearch documents to Elasticsearch index
     *
     * @param array $documents
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     * @throws \Exception
     */
    public function addDocs(array $documents, $storeId, $mappedIndexerId)
    {
        if (count($documents)) {
            try {
                $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
                $bulkIndexDocuments = $this->getDocsArrayInBulkIndexFormat($documents, $indexName);
                $this->client->bulkQuery($bulkIndexDocuments);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Removes all documents from Elasticsearch index
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     */
    public function cleanIndex($storeId, $mappedIndexerId)
    {
        // needed to fix bug with double indices in alias because of second reindex in same process
        unset($this->preparedIndex[$storeId]);

        $this->checkIndex($storeId, $mappedIndexerId, true);
        $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);

        // prepare new index name and increase version
        $indexPattern = $this->indexNameResolver->getIndexPattern($storeId, $mappedIndexerId);
        $version = (int)(str_replace($indexPattern, '', $indexName));
        $newIndexName = $indexPattern . (++$version);

        // remove index if already exists
        if ($this->client->indexExists($newIndexName)) {
            $this->client->deleteIndex($newIndexName);
        }

        // prepare new index
        $this->prepareIndex($storeId, $newIndexName, $mappedIndexerId);

        return $this;
    }

    /**
     * Delete documents from Elasticsearch index by Ids
     *
     * @param array $documentIds
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     * @throws \Exception
     */
    public function deleteDocs(array $documentIds, $storeId, $mappedIndexerId)
    {
        try {
            $this->checkIndex($storeId, $mappedIndexerId, false);
            $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
            $bulkDeleteDocuments = $this->getDocsArrayInBulkIndexFormat(
                $documentIds,
                $indexName,
                self::BULK_ACTION_DELETE
            );
            $this->client->bulkQuery($bulkDeleteDocuments);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }

        return $this;
    }

    /**
     * Reformat documents array to bulk format
     *
     * @param array $documents
     * @param string $indexName
     * @param string $action
     * @return array
     */
    protected function getDocsArrayInBulkIndexFormat(
        $documents,
        $indexName,
        $action = self::BULK_ACTION_INDEX
    ) {
        $bulkArray = [
            'index' => $indexName,
            'type' => $this->clientConfig->getEntityType(),
            'body' => [],
            'refresh' => true,
        ];

        foreach ($documents as $id => $document) {
            $bulkArray['body'][] = [
                $action => [
                    '_id' => $id,
                    '_type' => $this->clientConfig->getEntityType(),
                    '_index' => $indexName
                ]
            ];
            if ($action == self::BULK_ACTION_INDEX) {
                $bulkArray['body'][] = $document;
            }
        }

        return $bulkArray;
    }

    /**
     * Checks whether Elasticsearch index and alias exists.
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @param bool $checkAlias
     *
     * @return $this
     */
    public function checkIndex(
        $storeId,
        $mappedIndexerId,
        $checkAlias = true
    ) {
        // create new index for store
        $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
        if (!$this->client->indexExists($indexName)) {
            $this->prepareIndex($storeId, $indexName, $mappedIndexerId);
        }

        // add index to alias
        if ($checkAlias) {
            $namespace = $this->indexNameResolver->getIndexNameForAlias($storeId, $mappedIndexerId);
            if (!$this->client->existsAlias($namespace, $indexName)) {
                $this->client->updateAlias($namespace, $indexName);
            }
        }
        return $this;
    }

    /**
     * Update Elasticsearch alias for new index.
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     */
    public function updateAlias($storeId, $mappedIndexerId)
    {
        if (!isset($this->preparedIndex[$storeId])) {
            return $this;
        }

        $oldIndex = $this->indexNameResolver->getIndexFromAlias($storeId, $mappedIndexerId);
        if ($oldIndex == $this->preparedIndex[$storeId]) {
            $oldIndex = '';
        }

        $this->client->updateAlias(
            $this->indexNameResolver->getIndexNameForAlias($storeId, $mappedIndexerId),
            $this->preparedIndex[$storeId],
            $oldIndex
        );

        // remove obsolete index
        if ($oldIndex) {
            $this->client->deleteIndex($oldIndex);
        }

        return $this;
    }

    /**
     * Create new index with mapping.
     *
     * @param int $storeId
     * @param string $indexName
     * @param string $mappedIndexerId
     * @return $this
     */
    protected function prepareIndex($storeId, $indexName, $mappedIndexerId)
    {
        $this->indexBuilder->setStoreId($storeId);
        $settings = $this->indexBuilder->build();
        $allAttributeTypes = $this->getAllAttributesTypes();

        $settings['index']['mapping']['total_fields']['limit'] = $this->getMappingTotalFieldsLimit($allAttributeTypes);
        $this->client->createIndex($indexName, ['settings' => $settings]);
        $this->client->addFieldsMapping(
            $allAttributeTypes,
            $indexName,
            $this->clientConfig->getEntityType()
        );
        $this->preparedIndex[$storeId] = $indexName;
        return $this;
    }

    /**
     * Get total fields limit for mapping.
     *
     * @param array $allAttributeTypes
     * @return int
     */
    private function getMappingTotalFieldsLimit(array $allAttributeTypes): int
    {
        return count($allAttributeTypes) + self::MAPPING_TOTAL_FIELDS_BUFFER_LIMIT;
    }

    /**
     * @return array
     */
    private function getAllAttributesTypes()
    {
        $getAllAttributesTypes = [
            'creation_time' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd'],
            'update_time' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd'],
            'page_id' => ['type' => 'integer'],
            'is_active' => ['type' => 'text',
                'fields' =>
                    ['keyword' => ['type' => 'keyword', 'ignore_above' => '256']]
            ],
            'title' => [
                'type' => 'text',
                'analyzer' => 'standard'
            ],
            'identifier' => [
                'type' => 'text',
                'analyzer' => 'standard'
            ],
            'content_heading' => [
                'type' => 'text',
                'analyzer' => 'standard'
            ],
            'content' => [
                'type' => 'text',
                'analyzer' => 'standard'
            ]
        ];
        return $getAllAttributesTypes;
    }
}
