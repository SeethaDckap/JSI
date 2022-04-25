<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Query as SearchQuery;
use Magento\Framework\App\Helper\Context;
use Magento\Cms\Model\PageFactory;

/**
 * Search Suite Autocomplete config data helper
 */
class Autosuggest extends AbstractHelper
{
    const XML_PATH_RECENT_SEARCH_ENABLED = 'catalog/search/ecc_recent_search_enabled';

    const XML_PATH_RECENT_SHOW_ON = 'catalog/search/ecc_recent_search_showon';

    const XML_PATH_RECENT_SEARCH_TITLE = 'catalog/search/ecc_recent_search_title';

    const XML_PATH_HOT_SEARCH_ENABLED = 'catalog/search/ecc_hot_search_enabled';

    const XML_PATH_HOT_SHOW_ON = 'catalog/search/ecc_hot_search_showon';

    const XML_PATH_HOT_SEARCH_TITLE = 'catalog/search/ecc_hot_search_title';

    /**
     * @var PageFactory
     */
    private $cmsPage;

    /**
     * Autosuggest constructor.
     *
     * @param Context     $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->cmsPage = $pageFactory;
    }

    /**
     * Retrieve auto suggest url
     *
     * @return string
     */
    public function getAutoSuggestUrl()
    {
        return $this->_getUrl(
            'eccsearch/ajax/suggest',
            ['_secure' => $this->_getRequest()->isSecure()]
        );
    }

    /**
     * Retrieve list of autosuggest fields
     *
     * @param int|null $storeId
     * @return array
     */
    public function getAutocompleteFieldsAsArray()
    {
        return array('didyoumean');
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
        return $this->_getUrl(
            'catalogsearch/result',
            ['_query' => [QueryFactory::QUERY_VAR_NAME => $query], '_secure' => $this->_request->isSecure()]
        );
    }

    /**
     * Retrieve minimum query length
     *
     * @param mixed $store
     * @return int|string
     */
    public function getMinQueryLength($store = null)
    {
        return $this->scopeConfig->getValue(
            SearchQuery::XML_PATH_MIN_QUERY_LENGTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if Recent Search is Enabled/Disabled
     * @return mixed
     */
    public function isRecentSearchEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_RECENT_SEARCH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns where the recent search needs to be shown
     * @return mixed
     */
    public function getRecentSearchConfig()
    {
        return $this->scopeConfig->getValue(
        self::XML_PATH_RECENT_SHOW_ON,
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the tile for Recent Search
     * @return mixed
     */
    public function getRecentSearchTitle()
    {
        $title = '';
        if ($this->isRecentSearchEnabled()) {
            $title = $this->scopeConfig->getValue(
                self::XML_PATH_RECENT_SEARCH_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $title;
    }

    /**
     * Check if Hot Search is Enabled/Disabled
     * @return bool
     */
    public function isHotSearchEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_HOT_SEARCH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns where/when the hot search needs to be shown
     * @return mixed
     */
    public function getHotSearchConfig()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_HOT_SHOW_ON,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the tile for Hot Search
     * @return mixed
     */
    public function getHotSearchTitle()
    {
        $title = '';
        if ($this->isHotSearchEnabled()) {
            $title = $this->scopeConfig->getValue(
                self::XML_PATH_HOT_SEARCH_TITLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $title;
    }

    /**
     * Get All CMS Page Id's.
     *
     * @return array
     */
    public function getAllCmsPageIds()
    {
        $collection = $this->cmsPage->create()->getCollection();
        return $collection->getAllIds();;
    }
}
