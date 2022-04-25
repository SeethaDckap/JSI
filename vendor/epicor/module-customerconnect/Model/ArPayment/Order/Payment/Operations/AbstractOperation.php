<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Order\Payment\Operations;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Epicor\Customerconnect\Api\Data\OrderInterface;
use Epicor\Customerconnect\Api\Data\OrderPaymentInterface;
use Epicor\Customerconnect\Model\ArPayment\Order\Invoice;
use Epicor\Customerconnect\Model\ArPayment\Order\Payment\Transaction\BuilderInterface;
use Epicor\Customerconnect\Model\ArPayment\Order\Payment\Transaction\ManagerInterface;

/**
 * Class AbstractOperation
 */
abstract class AbstractOperation
{

    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var ManagerInterface
     */
    protected $transactionManager;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @param CommandInterface $stateCommand
     * @param BuilderInterface $transactionBuilder
     * @param ManagerInterface $transactionManager
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        BuilderInterface $transactionBuilder,
        ManagerInterface $transactionManager,
        EventManagerInterface $eventManager
    ) {
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionManager = $transactionManager;
        $this->eventManager = $eventManager;
    }

    /**
     * Create new invoice with maximum qty for invoice for each item
     * register this invoice and capture
     *
     * @param OrderPaymentInterface $payment
     * @return Invoice
     */
    protected function invoice(OrderPaymentInterface $payment)
    {
        /** @var Invoice $invoice */
        $invoice = $payment->getOrder()->prepareInvoice();

        $invoice->register();
        if ($payment->getMethodInstance()->canCapture()) {
            $invoice->capture();
        }

        $payment->getOrder()->addRelatedObject($invoice);
        return $invoice;
    }

    /**
     * Totals updater utility method
     * Updates self totals by keys in data array('key' => $delta)
     *
     * @param OrderPaymentInterface $payment
     * @param array $data
     * @return void
     */
    protected function updateTotals(OrderPaymentInterface $payment, $data)
    {
        foreach ($data as $key => $amount) {
            if (null !== $amount) {
                $was = $payment->getDataUsingMethod($key);
                $payment->setDataUsingMethod($key, $was + $amount);
            }
        }
    }

    /**
     * Return invoice model for transaction
     *
     * @param OrderInterface $order
     * @param string $transactionId
     * @return false|Invoice
     */
    protected function getInvoiceForTransactionId(OrderInterface $order, $transactionId)
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getTransactionId() == $transactionId) {
                $invoice->load($invoice->getId());
                // to make sure all data will properly load (maybe not required)
                return $invoice;
            }
        }
        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->getState() == \Epicor\Customerconnect\Model\ArPayment\Order\Invoice::STATE_OPEN
                && $invoice->load($invoice->getId())
            ) {
                $invoice->setTransactionId($transactionId);
                return $invoice;
            }
        }
        return false;
    }
}
