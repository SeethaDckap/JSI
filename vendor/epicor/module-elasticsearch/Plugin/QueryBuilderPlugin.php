<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin;

use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;

class QueryBuilderPlugin
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->_request = $request;
    }

    public function afterInitQuery(
        \Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Query\Builder $subject,
        $result
    )
    {
        $subject;
        if ($this->_request->getFullActionName() == 'catalog_category_view') {
            return $result;
        }
        $ecc_max_results = $this->scopeConfig->getValue(
            'catalog/search/ecc_max_results',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $elasticsearchVersion = $this->scopeConfig->getValue(
            'catalog/search/engine',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $ecc_max_results = ($ecc_max_results && is_numeric($ecc_max_results)) ? $ecc_max_results : 250;

        // By default, each index in Elasticsearch 6 is allocated 5 primary shards
        // In Elasticsearch 7 terminate_after is used to limit the results
        if ($elasticsearchVersion == 'elasticsearch6') {
            $ecc_max_results = round($ecc_max_results / 5);
        }
        if ($result) {
            $result['terminate_after'] = $ecc_max_results;
        }
        return $result;
    }

}
