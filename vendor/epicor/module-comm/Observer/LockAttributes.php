<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class LockAttributes extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $product->lockAttribute('ecc_stk_type');
        $product->lockAttribute('ecc_configurable_part_price');
    }

}