<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin;

use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;

class FilterBuilderPlugin
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function beforeBuild(
        \Magento\Elasticsearch\SearchAdapter\Filter\Builder $subject,
        \Magento\Framework\Search\Request\FilterInterface $filter,
        $conditionType
    )
    {
        $andOr = $this->scopeConfig->getValue(
            'catalog/search/ecc_and_or',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$andOr) {
            $conditionType = \Magento\Elasticsearch\SearchAdapter\Filter\BuilderInterface::FILTER_QUERY_CONDITION_MUST;
        }
        return [$filter, $conditionType];
    }
}