<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin\CatalogSearch\Advanced;

class FilterProducts
{

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * FilterProducts constructor.
     * @param \Epicor\Comm\Helper\Locations $commLocationsHelper
     * @param \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper
     * @param \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->commLocationsHelper = $commLocationsHelper;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Apply list & Location filtering before collection load
     * with elasticSearch
     *
     * @param \Epicor\Elasticsearch\Model\ResourceModel\Advanced\Collection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(
        \Epicor\Elasticsearch\Model\ResourceModel\Advanced\Collection $collection,
        $printQuery = false,
        $logQuery = false
    ) {
        $this->applyLocationFilter($collection);
        $this->applyListFilter($collection);
        return [$printQuery, $logQuery];
    }


    /**
     * Applies location filtering if locations is enabled to elasticSearch
     * Note: not applicable for mySql
     *
     * @param \Epicor\Elasticsearch\Model\ResourceModel\Advanced\Collection $collection
     */
    public function applyLocationFilter(\Epicor\Elasticsearch\Model\ResourceModel\Advanced\Collection $collection)
    {
        if ($this->commLocationsHelper->isLocationsEnabled() == false ||
            $collection->getFlag('no_product_filtering') ||
            $collection->getFlag('no_location_filtering') ||
            $collection->getFlag('location_sql_applied') ||
            $this->_isMySqlEngine()
        ) {
            return;
        }

        $isLocationRequireForConfigurable = $this->commLocationsHelper->isLocationRequireForConfigurable();
        $locationString = $this->commLocationsHelper->getCustomerDisplayLocationCodes();
        if (!$isLocationRequireForConfigurable) {
            $locationString[] = "NULL";
        }

        //Add Location filter for searchCriteria
        $collection->setCustomFilter(\Epicor\Elasticsearch\Model\Adapter\BatchDataMapper\LocationFieldsProvider::ECC_LOCATION_CODE_FIELD_NAME,
            $locationString);

        $collection->setFlag('location_sql_applied', true);
    }

    /**
     * Applies lists filtering if lists is enabled
     *
     * @param \Epicor\Elasticsearch\Model\ResourceModel\Advanced\Collection $collection
     */
    private function applyListFilter(\Epicor\Elasticsearch\Model\ResourceModel\Advanced\Collection $collection)
    {
        if ($this->listsFrontendProductHelper->listsDisabled() ||
            $collection->getFlag('no_product_filtering') ||
            $collection->getFlag('lists_sql_applied') ||
            $this->_isMySqlEngine()
        ) {
            return;
        }

        if ($this->listsFrontendProductHelper->hasFilterableLists() || $this->listsFrontendContractHelper->mustFilterByContract()) {
            $productIds = $this->listsFrontendProductHelper->getActiveListsProductIds(true);
            //Add list filter for searchCriteria
            $collection->setCustomFilter("list_id", $productIds);
            $collection->setFlag('lists_sql_applied', true);
        }
    }

    /**
     * @return bool
     */
    private function _isMySqlEngine()
    {
        $searchEngine = $this->scopeConfig->getValue(
            'catalog/search/engine', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return ($searchEngine == "mysql") ? true : false;
    }

}
