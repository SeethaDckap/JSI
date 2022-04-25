<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class HomePageRedirectCheck extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove Items from the category menu if they have no products and auto hide is enabled
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $autoHideHelper = $this->commonHelper;
        /* @var $autoHideHelper Epicor_Common_Helper_Data */
        $autoHideEnabled = $autoHideHelper->getAutohideCategories();
        if ($autoHideEnabled) {
            $currentCategory = $this->registry->registry('current_category')->getEntityId();
            $productCollection = $this->filterCategoryCollection($currentCategory);

            $count = $productCollection->getSize();
            if ($count == 0) {
                //M1 > M2 Translation Begin (Rule p2-3)
                //Mage::app()->getResponse()->setRedirect('/');
                $this->response->setRedirect('/');
                $this->response->sendResponse();
                //M1 > M2 Translation End
            }
        }
    }

}