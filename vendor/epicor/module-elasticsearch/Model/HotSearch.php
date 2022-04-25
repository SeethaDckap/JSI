<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model;

/**
 * Class HotSearch
 * @package Epicor\Elasticsearch\Model
 */
class HotSearch extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Max Recent Search Query
     */
    const XML_PATH_HOT_MAX_RESULTS = 'catalog/search/ecc_hot_search_maxresults';

    /**
     * Search Queries
     */
    const XML_PATH_HOT_SEARCH_QUERIES = 'catalog/search/ecc_hot_search_queries';

    /**
     * Queries that need to ignored
     */
    const XML_PATH_HOT_IGNORE_QUERIES = 'catalog/search/ecc_hot_ignore_queries';

    /**
     * @var \Magento\Search\Model\ResourceModel\Query\CollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $hotSearches;

    /**
     * @var int
     */
    private $minPopularity;

    /**
     * @var int
     */
    private $maxPopularity;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * HotSearch constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Comm\Model\Context $eccContext
     * @param \Magento\Search\Model\ResourceModel\Query\CollectionFactory $queryCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Context $eccContext,
        \Magento\Search\Model\ResourceModel\Query\CollectionFactory $queryCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->scopeConfig = $eccContext->getScopeConfig();
        $this->storeManager = $eccContext->getStoreManager();
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Returns Max Results for Hot Searches
     * @return mixed
     */
    private function getHsMaxResults()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HOT_MAX_RESULTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Search Queries from Admin Conig
     * @return mixed
     */
    private function getHotSearchQueries()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HOT_SEARCH_QUERIES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the words that need to be ignored from Hot Searches
     * @return mixed
     */
    private function getIgnoredWords()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HOT_IGNORE_QUERIES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the Hot Search Queries
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHotSearches()
    {
        $this->getQueries();
        return $this->hotSearches;
    }

    /**
     * Sets the Hot Search Queries
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQueries()
    {
        $hotSearchQueries = $this->getHotSearchQueries();
        $hotSearchQueries = trim($hotSearchQueries);

        $ignoredQueries = $this->getIgnoredWords();
        $ignoredQueries = trim($ignoredQueries);
        if ($hotSearchQueries != '') {
            $this->getConfiguredQueries($hotSearchQueries, $ignoredQueries);
        } else {
            $this->loadHotSearches($ignoredQueries);
        }
        return $this;
    }

    /**
     * Returns the hot searches set in admin configuration
     * @param string $hotSearchQueries
     * @param string $ignoredQueries
     * @return $this
     */
    private function getConfiguredQueries($hotSearchQueries, $ignoredQueries)
    {
        $maxResults = $this->getHsMaxResults();
        $hotSearchQueries = explode(",", $hotSearchQueries);
        $hotSearchQueries = array_filter($hotSearchQueries);
        $hotSearchQueries = array_map('trim', $hotSearchQueries);
        if ($ignoredQueries != '') {
            $ignoredQueries = explode(",", $ignoredQueries);
            $ignoredQueries = array_filter($ignoredQueries);
            $ignoredQueries = array_map('trim', $ignoredQueries);
            $hotSearchQueries = array_diff($hotSearchQueries, $ignoredQueries);
        }
        $this->hotSearches = array_slice($hotSearchQueries, 0, $maxResults);
        return $this;
    }

    /**
     * Loads Popular search terms
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function loadHotSearches($ignoredQueries)
    {
        if (empty($this->hotSearches)) {
            $maxResults = $this->getHsMaxResults();
            $this->hotSearches = [];
            $collection = $this->queryCollectionFactory->create()
                ->setPopularQueryFilter($this->storeManager->getStore()->getId());
            if ($ignoredQueries != '') {
                $ignoredQueries = explode(",", $ignoredQueries);
                $ignoredQueries = array_filter($ignoredQueries);
                $ignoredQueries = array_map('trim', $ignoredQueries);
                $collection->addFieldToFilter('query_text', ['nin' => $ignoredQueries]);
            }
            $collection->setPageSize(100)
                ->load();

            $terms = $collection->getItems();
            if (count($terms) == 0) {
                return $this;
            }

            $this->maxPopularity = reset($terms)->getPopularity();
            $this->minPopularity = end($terms)->getPopularity();
            $range = $this->maxPopularity - $this->minPopularity;
            $range = $range == 0 ? 1 : $range;
            $termKeys = [];
            foreach ($terms as $term) {
                if (!$term->getPopularity()) {
                    continue;
                }
                $term->setRatio(($term->getPopularity() - $this->minPopularity) / $range);
                $temp[$term->getQueryText()] = $term;
                $termKeys[] = $term->getQueryText();
            }

            foreach ($termKeys as $termKey) {
                $this->hotSearches[$termKey] = $temp[$termKey]->getQueryText();
            }
            $this->hotSearches = array_slice($this->hotSearches, 0, $maxResults);
        }
        return $this;
    }
}