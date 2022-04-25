<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer\Msq;

class RemoveOutOfStock extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $module = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $collection = $observer->getEvent()->getDataObject()->getProducts();
        $remove = $this->registry->registry('hide_out_of_stock_product');
        if ($remove && !$this->registry->registry('current_product') && $module != 'wishlist' && $controller != 'product_compare') {
            foreach ($remove as $key) {
                if(is_array($collection) || (method_exists($collection, 'getId') && $collection->getId())){
                    unset($collection[$key]);
                }else{
                    $collection->removeItemByKey($key);
                    /* remove out stock product from collection query(layered navigation) */
                    $collection->addAttributeToFilter('entity_id', array('nin' => $remove));
                }
            }
        }
        return $collection;
    }

}