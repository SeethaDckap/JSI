<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class AuthorisedCheck extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($order->getPayment()->getBaseAmountAuthorized() >= $order->getPayment()->getBaseAmountOrdered() ||
            ( // SagePay Authenticated Check
            $order->getSagepayInfo() &&
            (
            in_array($order->getSagepayInfo()->getStatus(), array('OK', 'AUTHENTICATED', 'REGISTERED')) ||
            in_array($order->getSagepayInfo()->getTxStateId(), array('14', '15', '16', '21'))
            )
            )
        ) {
            $this->sendBsvAndGor($order);
        }
    }

}