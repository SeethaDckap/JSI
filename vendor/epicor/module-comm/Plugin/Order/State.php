<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Order;


use Magento\Sales\Model\Order;

/**
 * Class State
 */
class State
{
        
    public function aroundCheck(
        \Magento\Sales\Model\ResourceModel\Order\Handler\State $subject,
        \Closure $proceed,
        Order $order
    ) {
        $result = $proceed($order);
        
        if ($order->getManualState()) {
            $order->setData('state', $order->getManualState());
        }
        if ($order->getManualStatus()) {
            $order->setData('status', $order->getManualStatus());
        }
        
        return $result;
    }    
}
