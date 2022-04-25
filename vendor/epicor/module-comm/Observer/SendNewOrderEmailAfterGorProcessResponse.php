<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SendNewOrderEmailAfterGorProcessResponse extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        /* @var $order Epicor_Comm_Model_Order */

        if (!$order->getEmailSent() && $order->getEccErpOrderNumber()) {
            $order->sendNewOrderEmail();
        }
    }

}