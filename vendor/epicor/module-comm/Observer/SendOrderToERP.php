<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Epicor\Comm\Helper\BsvAndGor;
use Epicor\OrderApproval\Model\Status\Options as ApprovalStatus;

class SendOrderToERP extends AbstractObserver
    implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var BsvAndGor
     */
    protected $bsvAndGorHelper;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var ApprovalStatus
     */
    private $approvalStatus;

    /**
     * SendOrderToERP constructor.
     *
     * @param BsvAndGor     $bsvAndGorHelper
     * @param OrderResource $orderResource
     */
    public function __construct(
        BsvAndGor $bsvAndGorHelper,
        OrderResource $orderResource,
        ApprovalStatus $approvalStatus
    ) {
        $this->bsvAndGorHelper = $bsvAndGorHelper;
        $this->orderResource = $orderResource;
        $this->approvalStatus = $approvalStatus;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this|void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $all_orders = array();
        $order_data = $observer->getEvent()->getOrder();

        if ( ! empty($order_data) && $order_data != null) {
            $all_orders[] = $order_data;
        } else {
            /* If order is multi-shipping order then get order id from order array from observer param */
            $orders = $observer->getEvent()->getData('orders');
            if (count($orders) > 0) {
                $all_orders = $orders;
            }
        }

        foreach ($all_orders as $order) {
            if ($order->getIsApprovalPending() == 0) {//Validate order approval
                $this->bsvAndGorHelper->SendOrderToErp($order);
            } elseif(is_null($order->getEccGorSent()) && $order->getIsApprovalPending() == 1 ) {
                //if not admin
                $this->updateEccApprovalStatus($order);
            }
        }

        return $this;
    }

    /**
     * Update Ecc order Status for pending approval.
     *
     * @param $order
     *
     * @throws \Exception
     */
    private function updateEccApprovalStatus($order)
    {
        if ($order->getEccGorSent() !== ApprovalStatus::ECC_ORDER_APPROVAL_REJECTED_GOR_STATE)
        {
            //save ecc for sent
            $order->setEccGorSent(ApprovalStatus::ECC_ORDER_APPROVAL_PENDING_GOR_STATE);
            $this->orderResource->saveAttribute($order, 'ecc_gor_sent');

            //save ecc gor message
            $statusDescription = $this->approvalStatus->stateDescriptions();
            $order->setEccGorMessage($statusDescription[ApprovalStatus::ECC_ORDER_APPROVAL_PENDING_GOR_STATE]);
            $this->orderResource->saveAttribute($order, 'ecc_gor_message');
        }
    }
}
