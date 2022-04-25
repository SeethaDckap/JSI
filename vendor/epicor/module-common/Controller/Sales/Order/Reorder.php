<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Sales\Order;

class Reorder extends \Epicor\Common\Controller\Sales\Order
{

    /**
     * Action for reorder
     */
    public function execute()
    {
        $result = $this->_loadValidOrder();
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $order = $this->_coreRegistry->registry('current_order');
        /* @var $order Epicor_Comm_Model_Order */
        if (!$order) {
            return;
        }

        if ($order->getEccErpOrderNumber()) {
            $this->_reorderErp($order);
        } else {
            $this->_reorderLocal($order);
        }
    }

    }
