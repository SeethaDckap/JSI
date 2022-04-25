<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Msq;

class FeaturedProduct extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper \Epicor\Comm\Helper\Messaging */

        $helper->sendMsq($observer->getEvent()->getProduct(), 'featured_product');
        //$product = $observer->getEvent()->getProduct(;
        //$product->setFinalPrice(($product->getCustomerPrice() != null) ? $product->getCustomerPrice() : $product->getFinalPrice());
        return $this;
    }

}