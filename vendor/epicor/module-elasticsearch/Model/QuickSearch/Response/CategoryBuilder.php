<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\QuickSearch\Response;

use Epicor\Elasticsearch\Api\QuickSearchResponseBuilderInterface;
use Epicor\Elasticsearch\Helper\Autosuggest as SearchHelperData;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Catalog\Api\CategoryRepositoryInterface as CategoryRepository;

/**
 * Implementation class that adds Category Section to Quick Search
 *
 */
class CategoryBuilder extends AbstractBuilder implements QuickSearchResponseBuilderInterface
{

    /**
     * Elasticsearch Type
     */
    const ELASTICSEARCH_TYPE_CATEGORY = 'category';

    /**
     * Category auto suggestion Enabled/Disabled Config Path
     */
    const XML_PATH_CONFIG_PATH_ENABLED = 'catalog/search/ecc_category_enabled';

    /**
     * Category Max Results Config Path
     */
    const XML_PATH_MAX_RESULTS = 'catalog/search/ecc_category_maxresults';

    /**
     * Category Name Length Config Path
     */
    const XML_PATH_NAME_LENGTH = 'catalog/search/ecc_category_namelength';

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var string[]
     */
    private $categoryFields = [
        'name',
        'url_key',
        'url_path'
    ];

    /**
     * @var array
     */
    private $categoryNames = [];

    /**
     * CategoryBuilder constructor.
     * @param QueryFactoryInterface $queryFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param ConnectionManager $connectionManager
     * @param StoreManager $storeManager
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        QueryFactoryInterface $queryFactory,
        ScopeConfigInterface $scopeConfig,
        Config $config,
        SearchIndexNameResolver $searchIndexNameResolver,
        ConnectionManager $connectionManager,
        StoreManager $storeManager,
        CategoryRepository $categoryRepository,
        SearchHelperData $searchHelperData
    )
    {
        $this->categoryRepository = $categoryRepository;
        parent::__construct(
            $scopeConfig,
            $config,
            $queryFactory,
            $searchIndexNameResolver,
            $storeManager,
            $connectionManager,
            $searchHelperData
        );
    }

    /**
     * @inheritdoc
     */
    public function buildQuickSearchResponse()
    {
        return $this->getItems($this->query);
    }

    /**
     * Gets Categories
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     */
    private function getItems(QueryInterface $query)
    {
        $categoryResult = ["category" => []];
        if ($this->isAutoSuggestionAllowed() && $query->getQueryText() != '') {
            $categoryOptions = $this->getCategoryOptions($query);
            $categoryResult = [];
            $nameLength = $this->getNameLength();
            if (is_array($categoryOptions) && isset($categoryOptions['hits']['hits'])) {
                foreach ($categoryOptions['hits']['hits']  as $hits) {
                    $data = isset($hits['_source']) ? $hits['_source'] : [];
                    if (empty($data) && $data['is_active'] != 1) {
                        continue;
                    }
                    $categoryResult[] =
                        [
                            'queryText' => $this->getCategoryName($data['name'], $nameLength),
                            'entity_id' => $data['entity_id'],
                            'categoryurl' => $this->getCategoryUrl($data),
                            'breadcrumb' => $this->getCategoryBreadcrumb($data)
                        ];
                }
            }
            $categoryResult = ["category" => $categoryResult];
        }
        return $categoryResult;
    }

    /**
     * Get Categories Options
     * @param QueryInterface $query
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryOptions(QueryInterface $query)
    {
        $searchQuery = $this->initQuery();
        $searchQuery['body']['query'] = array_merge(
            $searchQuery['body']['query'],
            $this->prepareQuery($query)
        );
        return $this->fetchQuery($searchQuery);
    }

    /**
     * Sorting of result
     * @return \string[][]
     */
    private function getSort()
    {
        return [
            '_score' => [
                'order' => 'desc'
            ],
            'entity_id' => [
                'order' => 'desc',
                'missing' => '_first',
                'unmapped_type' => 'keyword'
            ]
        ];
    }

    /**
     * Init Query
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function initQuery()
    {
        $searchSuggestionsCount = $this->getSearchSuggestionsCount();
        return [
            'index' => $this->searchIndexNameResolver->getIndexName(
                $this->storeManager->getStore()->getId(),
                self::ELASTICSEARCH_TYPE_CATEGORY
            ),
            'type' => Config::ELASTICSEARCH_TYPE_DOCUMENT,
            'body' => [
                'from' => 0,
                'size' => $searchSuggestionsCount,
                'query' => [],
                'sort' => $this->getSort()
            ]
        ];
    }

    /**
     * Prepare Search Query.
     * @param $query
     * @return array
     */
    private function prepareQuery($query)
    {
        return [
            'bool' => [
                BoolExpression::QUERY_CONDITION_SHOULD => $this->getCategoryQuery($query),
                'adjust_pure_negative' => true,
                'minimum_should_match' => "1",
                'boost' => 1
            ]
        ];
    }

    /**
     * Building Query
     * @param $query
     * @return array
     */
    private function getCategoryQuery($query)
    {
        $categoryQuery = [];
        $word = $query->getQueryText();
        $queries = explode(" ", $word);
        foreach ($this->categoryFields as $field) {
            foreach ($queries as $queryText) {
                $values = [];
                $queryText = strtolower($queryText) . '*';
                $values['wildcard'][$field]['wildcard'] = $queryText;
                $values['wildcard'][$field]['boost'] = 2;
                $categoryQuery[] = $values;
            }
        }
        return $categoryQuery;
    }

    /**
     * Gets Category Url
     * @param $data
     * @return string|void
     */
    private function getCategoryUrl($data)
    {
        if (!isset($data['url_path']) || !isset($data['entity_id'])) {
            return;
        }
        $categoryId = $data['entity_id'];
        if (!isset($this->categoryNames[$categoryId])) {
            $storeId = $this->storeManager->getStore()->getId();
            $this->categoryNames[$categoryId] = $this->categoryRepository->get($categoryId, $storeId);
        }
        return $this->categoryNames[$categoryId]->getUrl();
    }

    /**
     * Gets the breadcrumbs of the category
     * @param $data
     * @return string|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryBreadcrumb($data)
    {
        if (!isset($data['path'])) {
            return;
        }
        $rawPath = explode('/', $data['path']);
        $rawPath = array_slice($rawPath, 2);
        array_pop($rawPath);
        $breadcrumb = [];
        $storeId = $this->storeManager->getStore()->getId();
        $nameLength = $this->getNameLength();
        foreach ($rawPath as $categoryId) {
            $categoryName = html_entity_decode($this->getCategoryNameById($categoryId, $storeId));
            $breadcrumb[] = $this->getCategoryName($categoryName, $nameLength);
        }
        return implode(" > ", $breadcrumb);
    }

    /**
     * Gets Category Name
     * @param $categoryId
     * @param $storeId
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryNameById($categoryId, $storeId)
    {
        if (!isset($this->categoryNames[$categoryId])) {
            $this->categoryNames[$categoryId] = $this->categoryRepository->get($categoryId, $storeId);
        }
        return $this->categoryNames[$categoryId]->getName();
    }

    /**
     * Gets Category name
     * @param $categoryName
     * @param $length
     * @return string
     */
    private function getCategoryName($categoryName, $length)
    {
        if (strlen($categoryName) > $length) {
            $categoryName = $this->truncateData($categoryName, $length) . "...";
        }
        return $categoryName;
    }
}