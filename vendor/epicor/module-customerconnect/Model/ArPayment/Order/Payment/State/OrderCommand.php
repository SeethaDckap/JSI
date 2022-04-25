<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Order\Payment\State;

use Magento\Framework\App\ObjectManager;
use Epicor\Customerconnect\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Epicor\Customerconnect\Model\ArPayment\Order;
use Epicor\Customerconnect\Model\ArPayment\Order\StatusResolver;

class OrderCommand implements CommandInterface
{
    /**
     * @var StatusResolver
     */
    private $statusResolver;

    /**
     * @param StatusResolver|null $statusResolver
     */
    public function __construct(StatusResolver $statusResolver = null)
    {
        $this->statusResolver = $statusResolver
            ? : ObjectManager::getInstance()->get(StatusResolver::class);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param string|float $amount
     * @param OrderInterface $order
     * @return string
     */
    public function execute(OrderPaymentInterface $payment, $amount, OrderInterface $order)
    {
        $state = Order::STATE_PROCESSING;
        $status = null;
        $message = 'Ordered amount of %1';

        if ($payment->getIsTransactionPending()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $message = 'The order amount of %1 is pending approval on the payment gateway.';
        }

        if ($payment->getIsFraudDetected()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $status = Order::STATUS_FRAUD;
            $message = 'The order amount of %1 is pending approval on the payment gateway.';
        }

        if (!isset($status)) {
            $status = $this->statusResolver->getOrderStatusByState($order, $state);
        }

        $order->setState($state);
        $order->setStatus($status);

        return __($message, $order->getBaseCurrency()->formatTxt($amount));
    }

    /**
     * @deprecated 100.2.0 Replaced by a StatusResolver class call.
     *
     * @param Order $order
     * @param string $status
     * @param string $state
     * @return void
     */
    protected function setOrderStateAndStatus(Order $order, $status, $state)
    {
        if (!$status) {
            $status = $order->getConfig()->getStateDefaultStatus($state);
        }

        $order->setState($state)->setStatus($status);
    }
}
