<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Handler;

use Epicor\Customerconnect\Model\ArPayment\Order;

/**
 * Class State
 */
class State
{
    /**
     * Check order status before save
     *
     * @param Order $order
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function check(Order $order)
    {
//        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice() && !$order->canShip()) {
//            if (0 == $order->getBaseGrandTotal() || $order->canCreditmemo()) {
//                if ($order->getState() !== Order::STATE_COMPLETE) {
//                    $order->setState(Order::STATE_COMPLETE)
//                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
//                }
//            } elseif (floatval($order->getTotalRefunded())
//                || !$order->getTotalRefunded() && $order->hasForcedCanCreditmemo()
//            ) {
//                if ($order->getState() !== Order::STATE_CLOSED) {
//                    $order->setState(Order::STATE_CLOSED)
//                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
//                }
//            }
//        }
        if ($order->getState() == Order::STATE_NEW && $order->getIsInProcess()) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
        }
        return $this;
    }
}
