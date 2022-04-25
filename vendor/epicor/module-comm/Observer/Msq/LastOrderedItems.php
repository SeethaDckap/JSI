<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Msq;

class LastOrderedItems extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->commMessagingHelper;
        $msq = $helper->getCommMessageRequestMsqFactory()->create();
        if ($msq->isActive('triggers_last_ordered_items')) {
            /* @var $helper \Epicor\Comm\Helper\Messaging */
            $items = $observer->getEvent()->getOrderItemCollection();
            if (count($items) > 0 && !$this->registry->registry('processingSendOrderToERP')) {
                $products = array();
                foreach ($items as $item) {
                    $product = $item->getProduct();

                    if (($product instanceof \Magento\Catalog\Model\Product) == false) {
                        continue;
                    }

                    $products[] = $item->getProduct();
                }

                $helper->sendMsq($products, 'last_ordered_items');
            }
        }

        return $this;
    }

}