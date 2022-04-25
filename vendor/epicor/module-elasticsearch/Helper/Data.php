<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Elasticsearch Data helper
 *
 * @category   Epicor
 * @package    Elasticsearch
 * @author     Epicor Websales Team
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Generate search terms based on separators
     *
     * @param string $queryText
     * @return array
     */
    public function parseString($queryText)
    {
        $separators = $this->scopeConfig->getValue('catalog/search/ecc_separators',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //$tokenize_on_chars = [' '];
        //if ($separators) {
        //    $tokenize_on_chars = str_split($separators, 1);
        //    $tokenize_on_chars[] = ' ';
        //}

        $separators = str_replace("/", '\/', preg_quote($separators));
        $regexp = "/[[:space:]" . $separators . "]+/";
        //$find = array("'", '\[', '\]');
        //$replace = array("\'", '', '');
        //$this->_noWildcardPrefix = str_replace($find, $replace, "([ " . $separators . "]+|^)");
        //$this->_noWildcardSufix = str_replace($find, $replace, "([ " . $separators . "]+|$)");
        //$this->_queryText = addcslashes($queryText, "\000\n\r\\'\"\032");
        $words = array_unique(explode('|',
            trim(preg_replace($regexp, '|', $queryText), '|')));//str_replace($separators, '|', $queryText)
        return $words;
    }

    /**
     * Set Prefix and suffix for wildcard text search
     *
     * @param string $word
     * @return string
     */
    public function setPrefixAndSuffix($word)
    {
        $suffix = "";
        $prefix = "";
        $wildcardSuffix = $this->isWildcardSuffix();
        $wildcardPrefix = $this->isWildcardPrefix();
        if ($wildcardSuffix) {
            $suffix = "*";
        }
        if ($wildcardPrefix) {
            $prefix = "*";
        }
        $word = $prefix . $word . $suffix;
        return $word;
    }

    /**
     * Customer SKU Search Relevance
     *
     * @return int
     */
    public function getSkaSearchRelevance()
    {
        return $this->scopeConfig->getValue('epicor_search/general/cpn_search_weight') ?: 0;
    }

    /**
     * @return mixed
     */
    public function getIsOr()
    {
        $isOr = $this->scopeConfig->getValue('catalog/search/ecc_and_or',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $isOr;
    }

    /**
     * @return bool
     */
    public function isWildcardSuffix()
    {
        return $this->scopeConfig->getValue('catalog/search/ecc_wildcard_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isWildcardPrefix()
    {
        return $this->scopeConfig->getValue('catalog/search/ecc_wildcard_prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
