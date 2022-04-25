<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Msq;

class CompareProducts extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */
        $productHelper = $this->productHelper;
        /* @var $productHelper \Epicor\Comm\Helper\Product */

        $isLazyload = $productHelper->isLazyLoad();
        if (!$isLazyload &&
            $this->request->getActionName() == 'index' &&
            $this->request->getControllerName() == 'product_compare' &&
            $observer->getEvent()->getCollection() instanceof \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection
        ) {
            $helper->sendMsq($observer->getEvent()->getCollection(), 'compare_products');
        }

        return $this;
    }

}