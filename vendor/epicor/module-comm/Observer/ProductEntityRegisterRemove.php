<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProductEntityRegisterRemove extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        /* @var $product Epicor_Comm_Model_Product */

        $this->removeEntityRegistration($product, 'Product');

        $helper = $this->commEntityregHelper->create();
        /* @var $helper Epicor_Comm_Helper_Entityreg */

        if ($this->registry->registry('product_' . $product->getId() . '_related_tbd')) {
            $ids = $this->registry->registry('product_' . $product->getId() . '_related_tbd');
            foreach ($ids as $id) {
                $helper->removeEntityRegistration($product->getId(), 'Related', $id);
            }
        }

        if ($this->registry->registry('product_' . $product->getId() . '_crosssell_tbd')) {
            $ids = $this->registry->registry('product_' . $product->getId() . '_crosssell_tbd');
            foreach ($ids as $id) {
                $helper->removeEntityRegistration($product->getId(), 'CrossSell', $id);
            }
        }

        if ($this->registry->registry('product_' . $product->getId() . '_upsell_tbd')) {
            $ids = $this->registry->registry('product_' . $product->getId() . '_upsell_tbd');
            foreach ($ids as $id) {
                $helper->removeEntityRegistration($product->getId(), 'UpSell', $id);
            }
        }

        if ($this->registry->registry('product_' . $product->getId() . '_categoryid_tbd')) {
            $ids = $this->registry->registry('product_' . $product->getId() . '_categoryid_tbd');
            foreach ($ids as $id) {
                $helper->removeEntityRegistration($id, 'CategoryProduct', $product->getId());
            }
        }
    }

}