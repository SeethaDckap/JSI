<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class FilterCategoryCollection extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove Items from the category menu if they have no products and auto hide is enabled
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $this->catalogCategoryFactory->create()->load($categoryId);
        /* @var $category Mage_Catalog_Model_Category */
        $productCollection = $category->getProductCollection();
        $productCollection->addAttributeToFilter('visibility', array('in' => array(2, 4)));

        $productCollection = $this->commonHelper->performLocationProductFiltering($productCollection);
        $productCollection = $this->commonHelper->performContractProductFiltering($productCollection);
        return $productCollection;
    }

}