<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProductEntityRegisterBeforeDelete extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        /* @var $product Epicor_Comm_Model_Product */
        $related = $product->getRelatedProductIds();
        $crossSell = $product->getCrossSellProductIds();
        $upSell = $product->getUpSellProductIds();
        $cats = $product->getCategoryIds();

        $this->registry->register('product_' . $product->getId() . '_related_tbd', $related, true);
        $this->registry->register('product_' . $product->getId() . '_crosssell_tbd', $crossSell, true);
        $this->registry->register('product_' . $product->getId() . '_upsell_tbd', $upSell, true);
        $this->registry->register('product_' . $product->getId() . '_categoryid_tbd', $cats, true);
    }

}