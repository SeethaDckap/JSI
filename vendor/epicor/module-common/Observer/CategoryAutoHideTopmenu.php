<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class CategoryAutoHideTopmenu extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove Items from the category menu if they have no products and auto hide is enabled
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $autoHideHelper = $this->commonHelper->create();
        /* @var $autoHideHelper Epicor_Common_Helper_Data */
        $autoHideEnabled = $autoHideHelper->getAutohideCategories();
        if ($autoHideEnabled) {
            $menuTree = $observer->getEvent()->getMenu();
            $this->checkCategoryMenu($menuTree);
        }
    }

}