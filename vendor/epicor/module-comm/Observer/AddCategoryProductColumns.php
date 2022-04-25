<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class AddCategoryProductColumns extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Triggered when the reorder button is clicked 
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block->getType() == 'adminhtml/catalog_category_tab_product') {
            $block->addColumnAfter('visibility', array(
                'header' => 'Visibility',
                'type' => 'text',
                'index' => 'visibility',
                'width' => '150px',
                'renderer' => $this->commAdminhtmlCatalogCategoryTabRenderVisibilityFactory->create(),
                'filter-index' => 'visibility',
                ), 'name');
            $block->addColumnAfter('status', array(
                'header' => 'Status',
                'type' => 'text',
                'index' => 'status',
                'width' => '100px',
                'renderer' => $this->commAdminhtmlCatalogCategoryTabRenderStatusFactory->create(),
                'filter-index' => 'status',
                ), 'visibility');
        }
    }

}