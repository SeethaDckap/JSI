<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\QuickSearch\Response;

use Epicor\Elasticsearch\Api\QuickSearchResponseBuilderInterface;
use Epicor\Elasticsearch\Helper\Autosuggest as SearchHelperData;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Implementation class that adds Did You Mean Section to Quick Search
 */
class DidYouMeanBuilder extends AbstractBuilder implements QuickSearchResponseBuilderInterface
{
    /**
     * Did you mean Enabled/Disabled Config Path
     */
    const XML_PATH_CONFIG_PATH_ENABLED = 'catalog/search/ecc_didyoumean_enabled';

    /**
     * Did you mean Max Results Config Path
     */
    const XML_PATH_MAX_RESULTS = 'catalog/search/ecc_didyoumean_maxresults';

    /**
     * @var FieldProviderInterface
     */
    private $fieldProvider;

    /**
     * AbstractBuilder constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param QueryFactoryInterface $queryFactory
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param StoreManager $storeManager
     * @param FieldProviderInterface $fieldProvider
     * @param ConnectionManager $connectionManager
     * @param SearchHelperData $searchHelperData
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        QueryFactoryInterface $queryFactory,
        SearchIndexNameResolver $searchIndexNameResolver,
        StoreManager $storeManager,
        FieldProviderInterface $fieldProvider,
        ConnectionManager $connectionManager,
        SearchHelperData $searchHelperData
    )
    {
        $this->fieldProvider = $fieldProvider;
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
     * @param QueryInterface $query
     * @return \Magento\Search\Model\QueryResult[]
     */
    private function getItems(QueryInterface $query)
    {
        $didYouMeanResult = [];
        if ($this->isAutoSuggestionAllowed()) {
            foreach ($this->getDidYouMeanOptions($query) as $suggestion) {
                $didYouMeanResult[] =
                    [
                        'queryText' => $suggestion['text'],
                        'searchResultURL' => $this->searchHelperData->getResultUrl($suggestion['text'])
                    ];
            }
            $didYouMeanResult = ["didyoumean" => $didYouMeanResult];
        }
        return $didYouMeanResult;
    }

    /**
     * Get Did You Mean Options
     *
     * @param QueryInterface $query
     * @return array
     */
    private function getDidYouMeanOptions(QueryInterface $query)
    {
        $suggestions = [];
        $searchSuggestionsCount = $this->getSearchSuggestionsCount();
        $searchQuery = $this->initQuery($query);
        $searchQuery = $this->addDidYouMeanFields($searchQuery, $searchSuggestionsCount);
        $result = $this->fetchQuery($searchQuery);
        if (is_array($result)) {
            foreach ($result['suggest'] ?? [] as $suggest) {
                foreach ($suggest as $token) {
                    foreach ($token['options'] ?? [] as $key => $suggestion) {
                        $suggestions[$suggestion['score'] . '_' . $key] = $suggestion;
                    }
                }
            }
            ksort($suggestions);
            $texts = array_unique(array_column($suggestions, 'text'));
            $suggestions = array_slice(
                array_intersect_key(array_values($suggestions), $texts),
                0,
                $searchSuggestionsCount
            );
        }
        return $suggestions;
    }

    /**
     * Init Search Query.
     * @param string $query
     * @return array
     */
    private function initQuery($query)
    {
        $searchQuery = [
            'index' => $this->searchIndexNameResolver->getIndexName(
                $this->storeManager->getStore()->getId(),
                Config::ELASTICSEARCH_TYPE_DEFAULT
            ),
            'type' => Config::ELASTICSEARCH_TYPE_DEFAULT,
            'body' => [
                'suggest' => [
                    'text' => $query->getQueryText()
                ]
            ],
        ];
        return $searchQuery;
    }

    /**
     * Build Did You Mean on searchable fields.
     * @param array $searchQuery
     * @param int $searchSuggestionsCount
     * @return array
     */
    private function addDidYouMeanFields($searchQuery, $searchSuggestionsCount)
    {
        $fields = $this->getDidYouMeanFields();
        foreach ($fields as $field) {
            $searchQuery['body']['suggest']['phrase_' . $field] = [
                'phrase' => [
                    'field' => $field,
                    'analyzer' => 'standard',
                    'size' => $searchSuggestionsCount,
                    'max_errors' => 1,
                    'direct_generator' => [
                        [
                            'field' => $field,
                            'min_word_length' => 3,
                            'min_doc_freq' => 1,
                        ]
                    ],
                ],
            ];
        }
        return $searchQuery;
    }

    /**
     * Get fields to build Did You Mean section
     * @return array
     */
    private function getDidYouMeanFields()
    {
        $fields = array_filter($this->fieldProvider->getFields(), function ($field) {
            return (($field['type'] ?? null) === 'text') && (($field['index'] ?? null) !== false);
        });
        return array_keys($fields);
    }
}