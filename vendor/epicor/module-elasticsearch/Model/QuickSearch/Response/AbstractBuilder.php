<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\QuickSearch\Response;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryFactoryInterface;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Epicor\Elasticsearch\Helper\Autosuggest as SearchHelperData;

/**
 * Class DidYouMeanBuilder
 * @package Epicor\Elasticsearch\Model\QuickSearch\Response
 */
abstract class AbstractBuilder
{
    /**
     * Auto Suggestion Enabled/Disabled Config Path
     */
    const QUICK_SEARCH_AUTOSUGGESTION_ENABLED = 'catalog/search/ecc_autosuggestion_enabled';

    /**
     * Auto Suggestion block Enabled/Disabled Config Path
     */
    const XML_PATH_CONFIG_PATH_ENABLED = 'catalog/search/ecc_product_enabled';

    /**
     * Auto Suggestion block Max Results Config Path
     */
    const XML_PATH_MAX_RESULTS = 'catalog/search/ecc_product_maxresults';

    /**
     * Auto Suggestion Name Length Config Path
     */
    const XML_PATH_NAME_LENGTH = 'catalog/search/ecc_product_skulength';

    /**
     * Auto Suggestion Name Length Config Path
     */
    const XML_PATH_PRODUCT_NAME_LENGTH = 'catalog/search/ecc_product_namelength';

    /**
     * Auto Suggestion Name Length Config Path
     */
    const XML_PATH_RESULTS_BY = 'catalog/search/ecc_product_resultsby';

    /**
     * Auto Suggestion Description Length Config Path
     */
    const XML_PATH_DESCRIPTION_LENGTH = 'catalog/search/ecc_product_descriptionlength';

    /**
     * CMS Page Exclude
     */
    const XML_PATH_PAGE_EXCLUDE = 'catalog/search/ecc_cms_pages_ecxclude';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var QueryInterface
     */
    protected $query;

    /**
     * @var SearchIndexNameResolver
     */
    protected $searchIndexNameResolver;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var SearchHelperData
     */
    protected $searchHelperData;

    /**
     * @var int
     */
    protected $nameLength;

    /**
     * @var int
     */
    protected $descLength;

    /**
     * @var int
     */
    protected $productNameLength;

    /**
     * @var string
     */
    protected $resultsby;

    /**
     * AbstractBuilder constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param QueryFactoryInterface $queryFactory
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param StoreManager $storeManager
     * @param ConnectionManager $connectionManager
     * @param SearchHelperData $searchHelperData
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config,
        QueryFactoryInterface $queryFactory,
        SearchIndexNameResolver $searchIndexNameResolver,
        StoreManager $storeManager,
        ConnectionManager $connectionManager,
        SearchHelperData $searchHelperData
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->query = $queryFactory->get();
        $this->searchIndexNameResolver = $searchIndexNameResolver;
        $this->storeManager = $storeManager;
        $this->connectionManager = $connectionManager;
        $this->searchHelperData = $searchHelperData;
    }

    /**
     * Fetch Query.
     * @param array $query
     * @return array
     */
    protected function fetchQuery(array $query)
    {
        return $this->connectionManager->getConnection()->query($query);
    }

    /**
     * Sets Max Count for Did You Mean section.
     * @return int
     */
    protected function getSearchSuggestionsCount()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_MAX_RESULTS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Checks whether Elastic Search and Autosuggestion is enabled under Quick Search.
     * @return bool
     */
    protected function isAutoSuggestionAllowed()
    {
        $isAutoSuggestionEnabled = $this->scopeConfig->isSetFlag(
            self::QUICK_SEARCH_AUTOSUGGESTION_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        $isFeatureEnabled = $this->scopeConfig->isSetFlag(
            static::XML_PATH_CONFIG_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        $isElasticSearchEnabled = $this->config->isElasticsearchEnabled();
        $isAutoSuggestionAllowed = ($isElasticSearchEnabled && $isAutoSuggestionEnabled && $isFeatureEnabled);
        return $isAutoSuggestionAllowed;
    }

    /**
     * Truncates the given data
     * @param $data
     * @param $length
     * @return false|string
     */
    protected function truncateData($data, $length)
    {
        return substr($data, 0, $length);
    }

    /**
     * Returns Max length allowed for Name
     * @return int
     */
    protected function getNameLength()
    {
        if ($this->nameLength === null) {
            $this->nameLength = $this->scopeConfig->getValue(
                static::XML_PATH_NAME_LENGTH,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->nameLength;
    }

    /**
     * Returns Display Product Search Results by
     * @return int
     */
    protected function getResultsby()
    {
        if ($this->resultsby === null) {
            $this->resultsby = $this->scopeConfig->getValue(
                static::XML_PATH_RESULTS_BY,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->resultsby;
    }

    /**
     * Returns Max length allowed for Name
     * @return int
     */
    protected function getProductNameLength()
    {
        if ($this->productNameLength === null) {
            $this->productNameLength = $this->scopeConfig->getValue(
                static::XML_PATH_PRODUCT_NAME_LENGTH,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->productNameLength;
    }

    /**
     * Returns Max length allowed for Description
     * @return int
     */
    protected function getDescriptionLength()
    {
        if ($this->descLength === null) {
            $this->descLength = $this->scopeConfig->getValue(
                static::XML_PATH_DESCRIPTION_LENGTH,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->descLength;
    }

    /**
     * Exclude CMS Page ID.
     *
     * @return array
     */
    protected function getExcludePageId()
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_PAGE_EXCLUDE,
            ScopeInterface::SCOPE_STORE
        );
    }


}