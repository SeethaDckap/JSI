<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Lists;

class FilterProducts extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Filters products by lists
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        $collection = $observer->getEvent()->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */


        if (
            $helper->listsDisabled() ||
            $collection->getFlag('no_product_filtering') ||
            $collection instanceof \Magento\Bundle\Model\ResourceModel\Selection\Collection ||
            $collection->getFlag('lists_sql_applied')
        ) {
            return $this;
        }

        if ($helper->hasFilterableLists() || $contractHelper->mustFilterByContract()) {
            $productIds = $helper->getActiveListsProductIds();
            $collection->getSelect()->where(
                '(e.entity_id IN(' . $productIds . '))'
            );
        }
        $collection->setFlag('lists_sql_applied', true);
        return $this;
    }

}