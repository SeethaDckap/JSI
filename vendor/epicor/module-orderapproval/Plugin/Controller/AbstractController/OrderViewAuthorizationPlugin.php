<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Plugin\Controller\AbstractController;

use Magento\Sales\Controller\AbstractController\OrderViewAuthorization;
use Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface;
use Magento\Sales\Model\Order;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomer;
use Epicor\OrderApproval\Model\Approval\OrderApprovals;

class OrderViewAuthorizationPlugin
{
    /**
     * @var GroupCustomer
     */
    private $groupCustomer;

    /**
     * @var OrderApprovals
     */
    private $orderApprovals;

    /**
     * OrderViewAuthorizationPlugin constructor.
     * @param GroupCustomer $groupCustomer
     * @param OrderApprovals $orderApprovals
     */
    public function __construct(
        GroupCustomer $groupCustomer,
        OrderApprovals $orderApprovals
    ) {
        $this->groupCustomer = $groupCustomer;
        $this->orderApprovals = $orderApprovals;
    }

    /**
     * @param OrderViewAuthorization $subject
     * @param $proceed
     * @param Order $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundCanView(OrderViewAuthorization $subject, $proceed, Order $order)
    {
        $result = $proceed($order);

        if ($this->isOrderCustomerApprover($order)) {
            return true;
        }
        return $result;
    }

    /**
     * @param $order
     * @return bool
     */
    private function isOrderCustomerApprover($order)
    {
        $approverOrders = $this->orderApprovals->getApprovalOrderIds();

        return in_array($order->getId(), $approverOrders);
    }
}
