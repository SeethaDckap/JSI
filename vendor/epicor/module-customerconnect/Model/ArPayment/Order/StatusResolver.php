<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Order;

use Epicor\Customerconnect\Api\Data\OrderInterface;

class StatusResolver
{
    /**
     * @param OrderInterface $order
     * @param string $state
     * @return string
     */
    public function getOrderStatusByState(OrderInterface $order, $state)
    {
        $paymentMethodOrderStatus = $order->getPayment()->getMethodInstance()
            ->getConfigData('order_status');

        return array_key_exists($paymentMethodOrderStatus, $order->getConfig()->getStateStatuses($state))
            ? $paymentMethodOrderStatus
            : $order->getConfig()->getStateDefaultStatus($state);
    }
}
