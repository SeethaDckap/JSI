<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class PurgeCategoryProduct extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = $observer->getEvent()->getItems();

        foreach ($items as $item) {
            $product = $this->catalogProductFactory->create()->load($item->getEntityId());
            /* @var $product Epicor_Comm_Model_Product */

            $category = $this->catalogProductFactory->create()->load($item->getChildId());
            /* @var $category Mage_Catalog_Model_Category */

            if (!$product->isObjectNew()) {

                $params = array(
                    'entity' => $product,
                    'child' => $category,
                    'register' => $item
                );

                $this->eventManager->dispatch('epicor_comm_entity_purge_category_product_before', $params);

                $categories = array();

                foreach ($product->getCategoryIds() as $categoryId) {
                    if ($categoryId != $item->getChildId()) {
                        $categories[] = $categoryId;
                    }
                }

                $product->setCategoryIds($categories);
                $product->save();

                $this->eventManager->dispatch('epicor_comm_entity_purge_category_product_before', $params);
            }
        }
    }

}