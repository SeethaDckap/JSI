<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Msq;

class ProductDetails extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */

        $productHelper = $this->productHelper;
        /* @var $productHelper \Epicor\Comm\Helper\Product */
        $isLazyload = $productHelper->isLazyLoad();
        $moduleControllerAction = $this->request->getModuleName() . "_" . $this->request->getControllerName() . "_" . $this->request->getActionName();
        if($isLazyload && $moduleControllerAction == "catalog_product_view"){
            if($this->request->isAjax()){
                $helper->sendMsq($observer->getEvent()->getProduct(), 'product_details');
            }
        } else {
            $helper->sendMsq($observer->getEvent()->getProduct(), 'product_details');
        }

        return $this;
    }

}