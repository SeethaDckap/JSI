<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elasticsearch\Model\Category\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Epicor\Elasticsearch\Model\Category\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Class IndexStructure
 * @package Epicor\Elasticsearch\Model\Category\Indexer
 */
class IndexStructure implements IndexStructureInterface
{
    /**
     * @var ElasticsearchAdapter
     */
    private $adapter;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * IndexStructure constructor.
     * @param ElasticsearchAdapter $adapter
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        ElasticsearchAdapter $adapter,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->adapter = $adapter;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        $indexerId,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->cleanIndex($scopeId, $indexerId);
    }

    /**
     * {@inheritdoc}
     *
     */
    public function create(
        $indexerId,
        array $fields,
        array $dimensions = []
    ) {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->checkIndex($scopeId, $indexerId, false);
    }
}
