<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin;

class FilterProducts
{

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    private $listsresource;

    public function __construct(
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Lists\Model\ResourceModel\ListModel $listsresource
    )
    {
        $this->commLocationsHelper = $commLocationsHelper;
        $this->registry = $registry;
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->productMetadata = $productMetadata;
        $this->scopeConfig = $scopeConfig;
        $this->listsresource=$listsresource;
    }

    /**
     * Apply list & Location filtering before collection load
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param boolean $printQuery
     * @param boolean $logQuery
     *
     * @return array
     */
    public function beforeLoad(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection, $printQuery = false, $logQuery = false)
    {
        $this->applyLocationFilter($collection);
        $this->applyListFilter($collection);
        // NOTE: not ideal, but this needs to be doen or the collection size is not correct
        if ($this->productMetadata->getVersion() < '2.3.2') {
            $collection->getSize();
        }
        return [$printQuery, $logQuery];
    }

    /**
     * Apply list & Location filtering before collection get Size
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     *
     * @return array
     */
    public function beforeGetSize(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection)
    {
        $this->applyLocationFilter($collection);
        $this->applyListFilter($collection);

        return [];
    }

    /**
     * Override to force correct data in pagination
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param integer $result
     *
     * @return integer
     */
    public function afterGetSize(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection, $result)
    {
        if ($this->commLocationsHelper->isLocationsEnabled() == false ||
            $collection->getFlag('no_product_filtering') ||
            $collection->getFlag('no_location_filtering')
        ) {
            return $result;
        }
        if ($this->registry->registry('Epicor_Locations_Paging')) {
            return $this->registry->registry('Epicor_Locations_Paging');
        }

        $searchEngine = $this->scopeConfig->getValue(
            'catalog/search/engine', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($this->productMetadata->getVersion() > '2.3.1' && $searchEngine != 'mysql') {
            return $result;
        }
        return count($collection->getAllIdsCache());
    }

    /**
     * Applies location filtering if locations is enabled
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyLocationFilter(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection)
    {
        if ($this->commLocationsHelper->isLocationsEnabled() == false ||
            $collection->getFlag('no_product_filtering') ||
            $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection ||
            $collection->getFlag('no_location_filtering') ||
            $collection->getFlag('location_sql_applied')
        ) {
            return;
        }

        $locationTable = $collection->getTable('ecc_location_product');
        $configurableTypeCode = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
        $isLocationRequireForConfigurable = $this->commLocationsHelper->isLocationRequireForConfigurable();

        if ($this->_isMySqlEngine()) {
            $locationString = $this->commLocationsHelper->getEscapedCustomerDisplayLocationCodes();
            //This is need for Elastic search 6+ filtering with list
            if (!$isLocationRequireForConfigurable) {
                $collection->joinTable(
                    ['locations' => $locationTable],
                    'product_id = entity_id',
                    ['location_code'],
                    null,
                    "left"
                );
                $collection->getSelect()->where('(locations.location_code in (' . $locationString . ') or e.type_id = "' . $configurableTypeCode . '")');
            } else {
                $collection->joinTable(['locations' => $locationTable], 'product_id = entity_id', ['location_code']);
                $collection->getSelect()->where('location_code in (' . $locationString . ')');
            }
            $collection->groupByAttribute('entity_id');
        } else {
            $locationString = $this->commLocationsHelper->getCustomerDisplayLocationCodes();
            if (!$isLocationRequireForConfigurable) {
                $locationString[] = "NULL";
            }
            $collection->addFieldToFilter(
                \Epicor\Elasticsearch\Model\Adapter\BatchDataMapper\LocationFieldsProvider::ECC_LOCATION_CODE_FIELD_NAME,
                $locationString
            );
        }

        $collection->setFlag('location_sql_applied', true);
        $this->registry->unregister('location_sql_applied');
        $this->registry->register('location_sql_applied', true);
    }

    /**
     * Applies lists filtering if lists is enabled
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     *
     * @return void
     */
    private function applyListFilter(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection)
    {
        if ($this->listsFrontendProductHelper->listsDisabled() ||
            $collection->getFlag('no_product_filtering') ||
            $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection ||
            $collection->getFlag('lists_sql_applied')
        ) {
            return;
        }

        if ($this->listsFrontendProductHelper->hasFilterableLists() || $this->listsFrontendContractHelper->mustFilterByContract()) {
            if ($this->_isMySqlEngine()) {
                $productIds = $this->listsFrontendProductHelper->getActiveListsProductIds();
                // The below is needed for filtering to work on search page (doesnt seem to conflict with above!).
                // $collection->getSelect()->where('(e.entity_id IN(' . $productIds . '))').
                $collection->setFlag('lists_sql_applied', true);
                $productIdsd = explode(',', $productIds);
                $newArray    = false;

                foreach ($productIdsd as $val) {
                    $newArray[] = [$val];
                }

                $collection = $this->listsresource->applyListFilter($collection,$newArray);
            } else {
                $productIds = $this->listsFrontendProductHelper->getActiveListsProductIds(true);
                //This is need for Elastic search 6+ filtering with list
                $collection->addFieldToFilter("list_id", $productIds);
            }
            $collection->setFlag('lists_sql_applied', true);
        }
    }

    private function _isMySqlEngine()
    {
        $searchEngine = $this->scopeConfig->getValue(
            'catalog/search/engine', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return ($searchEngine == "mysql") ? true : false;
    }

}
