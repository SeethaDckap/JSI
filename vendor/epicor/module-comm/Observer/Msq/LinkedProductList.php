<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer\Msq;

class LinkedProductList extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */

        $type = $observer->getEvent()->getType();

        if ($this->canSendMsq($type)) {
            $helper->sendMsq($observer->getEvent()->getCollection(), 'linked_products_' . $type);
        }

        /* Below code does not exist in ECC1 so commented for now */
        /*
          if ($type == 'grouped') {
          $helper->sendMsq($observer->getEvent()->getCollection(), 'linked_products_' . $type);
          } else if ($action == 'view' & $controller == 'product') {
          $helper->sendMsq($observer->getEvent()->getCollection(), 'linked_products_' . $type);
          }
         */
        return $this;
    }

    protected function canSendMsq($type)
    {
        $module = $this->request->getModuleName();
        $action = $this->request->getActionName();
        $controller = $this->request->getControllerName();

        if ($type == 'grouped'
            && $module == 'wishlist'
            && $controller == 'index'
            && $action == 'index'
            && $this->accessRightHelper->isAllowed("Epicor_Customer::my_account_wishlist")
        ){
            return true;
        }

        if ($type == 'grouped'
            && (($controller != 'cart' && $action != 'add')
                || ($controller != 'quickadd' && $action != 'add')
            )
        ) {
            return true;
        }

        //product view
        if(($type == "related" || $type == "upsell" || $type == "substitute") && ($module == "catalog" && $controller == "product" && $action == "view")) {
            return true;
        }

        //cart page
        if(($type == "crosssell" || $type == "substitute") && ($module == "checkout" && $controller == "cart" && $action == "index")) {
            return true;
        }

        return false;
    }

}
