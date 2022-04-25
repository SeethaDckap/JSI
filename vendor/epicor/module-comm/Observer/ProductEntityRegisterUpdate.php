<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProductEntityRegisterUpdate extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->registry->registry('entity_register_update_product')) {
            $this->updateEntityRegistration($observer->getEvent()->getProduct(), 'Product');
        }
    }

}