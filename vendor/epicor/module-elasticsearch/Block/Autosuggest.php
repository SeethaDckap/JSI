<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Helper\Data;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Elasticsearch\Model\Config;

/**
 * Autosuggest block class
 */
class Autosuggest extends Template
{
    const XML_PATH_ECC_AUTOSUGGESTION_ENABLED = 'catalog/search/ecc_autosuggestion_enabled';
    const XML_PATH_DID_YOU_MEAN_TITLE = 'catalog/search/ecc_didyoumean_title';
    const XML_PATH_CATEGORY_TITLE = 'catalog/search/ecc_category_title';
    const XML_PATH_CMS_PAGE_TITLE = 'catalog/search/ecc_cms_pages_title';
    const XML_PATH_PRODUCT_TITLE = 'catalog/search/ecc_product_title';

    /**
     * @var Data
     */
    private $searchHelperData;

    /**
     * @var \Epicor\Elasticsearch\Helper\Autosuggest
     */
    private $epicorSearchHelperData;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Epicor\Elasticsearch\Model\RecentSearch
     */
    private $recentSearch;

    /**
     * @var Epicor\Elasticsearch\Model\HotSearch
     */
    private $hotSearchModel;

    /**
     * Autosuggest constructor.
     * @param Data $searchHelperData
     * @param \Epicor\Elasticsearch\Helper\Autosuggest $epicorSearchHelperData
     * @param Context $context
     * @param Config $config
     * @param \Epicor\Elasticsearch\Model\RecentSearch $recentSearch
     * @param array $data
     */
    public function __construct(
        Data $searchHelperData,
        \Epicor\Elasticsearch\Helper\Autosuggest $epicorSearchHelperData,
        Context $context,
        Config $config,
        \Epicor\Elasticsearch\Model\RecentSearch $recentSearch,
        \Epicor\Elasticsearch\Model\HotSearch $hotSearchModel,
        array $data = []
    ) {
        $this->searchHelperData = $searchHelperData;
        $this->epicorSearchHelperData = $epicorSearchHelperData;
        $this->scopeConfig = $context->getScopeConfig();
        $this->config = $config;
        $this->recentSearch =$recentSearch;
        $this->hotSearchModel = $hotSearchModel;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve auto suggest url
     *
     * @return string
     */
    public function getAutoSuggestUrl()
    {
        return $this->epicorSearchHelperData->getAutoSuggestUrl();
    }

    /**
     * Retrieve result page url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @param   string $query
     * @return  string
     */
    public function getResultUrl($query = null)
    {
        return $this->searchHelperData->getResultUrl($query);
    }

    /**
     * Retrieve minimum query length
     *
     * @param mixed $store
     * @return int|string
     */
    public function getMinQueryLength($store = null)
    {
        return $this->searchHelperData->getMinQueryLength($store);
    }

    /**
     * Get the tile for Did you mean auto suggestion
     * @return mixed
     */
    public function getDidYouMeanTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DID_YOU_MEAN_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the tile for Categories auto suggestion
     * @return mixed
     */
    public function getCategoryTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get relevant path to template
     * @return string|void
     */
    public function getTemplate()
    {
        $template = parent::getTemplate();
        $eccAutoSuggest = $this->scopeConfig->getValue(
            self::XML_PATH_ECC_AUTOSUGGESTION_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        $isElasticSearchEnabled = $this->config->isElasticsearchEnabled();
        if (!$isElasticSearchEnabled || !$eccAutoSuggest) {
            $template = '';
        }
        return $template;
    }

    /**
     * Get the tile for CMS Pages auto suggestion
     * @return mixed
     */
    public function getCmsPageTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CMS_PAGE_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the tile for Product auto suggestion
     * @return mixed
     */
    public function getProductTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the tile for Recent Search
     * @return mixed
     */
    public function getRecentSearchTitle()
    {
        return $this->epicorSearchHelperData->getRecentSearchTitle();
    }

    /**
     * Recent Searchs list
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRecentSearches()
    {
        $collection = $this->recentSearch->getRecentCollection();
        $recentSearches = "";
        $queryTexts = $collection->getColumnValues('query_text');
        if ($this->isRecentSearchEnabled() && !empty($queryTexts)) {
            $recentSearches = "<ul class='elsearchitems elrecsrchitems'>";
            foreach ($queryTexts as $query) {
                $queryUrl = $this->searchHelperData->getResultUrl($query);
                $query = $this->escapeHtml($query);
                $recentSearches .= "<li class='elsearchitem'><a href='" . $queryUrl . "' title='" . $query . "'>" . $query . "</a></li>";
            }
            $recentSearches .= "</ul>";
        }
        return $recentSearches;
    }

    /**
     * Returns where the recent search needs to be shown
     * @return mixed
     */
    public function getRecentSearchConfig()
    {
        return $this->epicorSearchHelperData->getRecentSearchConfig();
    }

    /**
     * Check if Recent Search is Enabled/Disabled
     * @return mixed
     */
    public function isRecentSearchEnabled()
    {
        return $this->epicorSearchHelperData->isRecentSearchEnabled();
    }

    /**
     * Get the tile for Hot Search
     * @return mixed
     */
    public function getHotSearchTitle()
    {
        return $this->epicorSearchHelperData->getHotSearchTitle();
    }

    /**
     * Hot Searches list
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHotSearches()
    {
        $hsHtml = "";
        if ($this->isHotSearchEnabled()) {
            $hotSearches = $this->hotSearchModel->getHotSearches();
            if (empty($hotSearches) === false) {
                $hsHtml = "<ul class='elsearchitems elrecsrchitems'>";
                foreach ($hotSearches as $hotSearch) {
                    $queryUrl = $this->searchHelperData->getResultUrl($hotSearch);
                    $query = $this->escapeHtml($hotSearch);
                    $hsHtml .= "<li class='elsearchitem'><a href='" . $queryUrl . "' title='" . $query . "'>" . $query . "</a></li>";
                }
                $hsHtml .= "</ul>";
            }

        }
        return $hsHtml;
    }

    /**
     * Returns whether the hot search needs to be shown
     * @return mixed
     */
    public function getHotSearchConfig()
    {
        return $this->epicorSearchHelperData->getHotSearchConfig();
    }

    /**
     * Check if Hot Search is Enabled/Disabled
     * @return mixed
     */
    public function isHotSearchEnabled()
    {
        return $this->epicorSearchHelperData->isHotSearchEnabled();
    }
}
