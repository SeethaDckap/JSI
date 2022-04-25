<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Rule\Condition\Product;

use Epicor\Elasticsearch\Model\Rule\Condition\Product as ProductCondition;
use Epicor\Elasticsearch\Model\Data\SearchQueryText;
/**
 * Build a search query from a search engine rule product condition.
 */
class QueryBuilder
{
    /**
     * @var SearchQueryText
     */
    private $searchQueryText;

    /**
     * QueryBuilder constructor.
     *
     * @param SearchQueryText $searchQueryText
     */
    public function __construct(
        SearchQueryText $searchQueryText
    ) {
        $this->searchQueryText = $searchQueryText;
    }

    /**
     * Build the query for a condition.
     *
     * @param ProductCondition $productCondition Product condition.
     *
     * @return array
     */
    public function getSearchQuery(ProductCondition $productCondition)
    {
        $query = null;
        $query = $this->getSpecialAttributesSearchQuery($productCondition);
        return $query;
    }

    /**
     * Create a query for special attribute.
     *
     * @param ProductCondition $productCondition Product condition.
     *
     * @return NULL|array
     */
    private function getSpecialAttributesSearchQuery(ProductCondition $productCondition)
    {
        $query = null;
        $buildSkuQuery = [];
        $conditionRuleFieldName = $productCondition->getAttribute();
        foreach (explode(',', $productCondition->getValue()) as $value)
        {
            if(!empty($value))
            {
                if($value == "*" && $this->searchQueryText->getQueryText()) {
                    $value = $this->searchQueryText->getQueryText();
                }
                $searchQueryParams = [
                    'query'                => trim($value),
                    'minimum_should_match' => "100%",
                    'boost'                => 1,
                ];
                $buildSkuSubQuery = ['match' => ["$conditionRuleFieldName" => $searchQueryParams]];
                if($productCondition->getOperator() === '()')
                {
                    $buildSkuQuery['should'][] = $buildSkuSubQuery;
                }
                else if($productCondition->getOperator() === '!()')
                {
                    $buildSkuQuery['must_not'][] = $buildSkuSubQuery;
                }
            }
        }
        if(isset($buildSkuQuery['should']))
        {
            $buildSkuQuery['minimum_should_match'] = 1;
        }
        if(empty($buildSkuQuery))
        {
            return null;
        }
        else
        {
            return $query = [
                'bool' => $buildSkuQuery
            ];
        }
    }
}
