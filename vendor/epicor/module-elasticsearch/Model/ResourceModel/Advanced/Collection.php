<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\ResourceModel\Advanced;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;

/**
 * Advanced search collection
 *
 * This collection should be refactored to not have dependencies on elasticSearch implementation.
 */
class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
{

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;


    /**
     * @param $attributeCode
     * @param $attributeValue
     * @return $this
     */
    public function setCustomFilter($attributeCode, $attributeValue)
    {
        $this->getFilterBuilder()->setField($attributeCode)->setValue($attributeValue);
        $this->getSearchCriteriaBuilder()->addFilter($this->getFilterBuilder()->create());
        return $this;
    }

    /**
     * Get search criteria builder.
     *
     * @return SearchCriteriaBuilder
     */
    private function getSearchCriteriaBuilder()
    {
        if (null === $this->searchCriteriaBuilder) {
            $this->searchCriteriaBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        }
        return $this->searchCriteriaBuilder;
    }

    private function getFilterBuilder()
    {
        if (null === $this->filterBuilder) {
            $this->filterBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Api\FilterBuilder::class);
        }
        return $this->filterBuilder;
    }
}
