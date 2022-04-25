<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Gor;

class ClearCustomerOrderRefSession extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->checkoutSession->unsetData('ecc_customer_order_ref');
        $this->checkoutSession->unsetData('ecc_tax_exempt_reference');
    }

}