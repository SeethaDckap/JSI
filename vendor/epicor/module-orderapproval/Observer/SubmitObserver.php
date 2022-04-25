<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Epicor\OrderApproval\Model\Approval\Email\Sender\ApprovalSender as OrderSender;
use Psr\Log\LoggerInterface;
use Epicor\OrderApproval\Model\Approval\Email\Sender\ApproverSender as ApproverSender;

/**
 * Class responsive for sending order approval emails when it's created through storefront.
 */
class SubmitObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var ApproverSender
     */
    private $approverSender;

    /**
     * SubmitObserver constructor.
     *
     * @param LoggerInterface $logger
     * @param OrderSender     $orderSender
     * @param ApproverSender  $approverSender
     */
    public function __construct(
        LoggerInterface $logger,
        OrderSender $orderSender,
        ApproverSender $approverSender
    ) {
        $this->logger = $logger;
        $this->orderSender = $orderSender;
        $this->approverSender = $approverSender;
    }

    /**
     * Send order approval email.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order && $order->getIsApprovalPending() == 1) {
            try {
                if ($this->orderSender->send($order)) {
                    $this->approverSender->send($order);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
