<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Msq;

class Wishlist extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->accessRightHelper->isAllowed("Epicor_Customer::my_account_wishlist")) {
            return;
        }
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */

        $productHelper = $this->productHelper;
        /* @var $productHelper \Epicor\Comm\Helper\Product */

        $isLazyload = $productHelper->isLazyLoad();
        $moduleControllerAction = $this->request->getModuleName() . "_" . $this->request->getControllerName() . "_" . $this->request->getActionName();
        $urlToIgnore = array('wishlist_index_index');
        if ($isLazyload) {
            if(in_array($moduleControllerAction, $urlToIgnore) && $this->request->isAjax()){
                $helper->sendMsq($observer->getEvent()->getProductCollection(), 'wishlist');
            } else if(!in_array($moduleControllerAction, $urlToIgnore)){ // Require this condition with side bar wishlist
                $helper->sendMsq($observer->getEvent()->getProductCollection(), 'wishlist');
            }
        } else {
            $helper->sendMsq($observer->getEvent()->getProductCollection(), 'wishlist');
        }

        return $this;
    }

}