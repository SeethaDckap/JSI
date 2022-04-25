<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Catalog\Pricing;

class RemovePriceFilter
{
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->commHelper = $commHelper;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Adds Decimal Validation
     *
     * @return array
     */
    public function afterGetList(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList $subject, \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection)
    {
        //hide price if ecc_hide_price enabled
        $hidePrice = $this->commHelper->isFunctionalityDisabledForCustomer('prices')
                    || $this->commHelper->getEccHidePrice();
        if ($hidePrice == 1) {
            $collection = $this->_prepareAttributeCollection($collection);
            $collection->removePriceFilter();
        }
        $collection->load();

        return $collection;
    }

    /**
     * Add filters to attribute collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->clear();
        $collection->addIsFilterableFilter();
        return $collection;
    }
}
