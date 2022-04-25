<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model;

/**
 * Class RecentSearch
 * @package Epicor\Elasticsearch\Model
 */
class RecentSearch extends \Magento\Catalog\Model\AbstractModel
{
    /**
     * Max Recent Search Query
     */
    const XML_PATH_RECENT_MAX_RESULTS = 'catalog/search/ecc_recent_search_maxresults';

    /**
     * @var \Magento\Search\Model\ResourceModel\Query\CollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * RecentSearch constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Search\Model\ResourceModel\Query\CollectionFactory $queryCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Search\Model\ResourceModel\Query\CollectionFactory $queryCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Returns Recent Searches Collection
     * @return \Magento\Search\Model\ResourceModel\Query\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRecentCollection()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $maxResults = $this->getRsMaxResults();
        $collection = $this->queryCollectionFactory->create();
        $collection->addStoreFilter($storeId);
        $collection->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('query_text')
            ->order(['updated_at desc'])
            ->limit($maxResults);
        return $collection;
    }

    /**
     * Returns Max Results for Recent Searches
     * @return mixed
     */
    private function getRsMaxResults()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RECENT_MAX_RESULTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}