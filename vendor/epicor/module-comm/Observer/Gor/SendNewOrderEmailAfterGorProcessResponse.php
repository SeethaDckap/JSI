<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Gor;

use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\App\RequestInterface;
class SendNewOrderEmailAfterGorProcessResponse extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderSender
     */
    protected $orderSender;
    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
        \Epicor\Comm\Helper\Sales\Order $commSalesOrderHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        RequestInterface $request
    ) { 
        parent::__construct(
            $commSalesOrderHelper,
            $checkoutSession,
            $commMessagingHelper,
            $commonHelper,
            $resourceConnection
        );
        $this->logger = $logger;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->request = $request;
    }
    
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        if ($event->getResult()) {
            $message = $event->getMessage();
            $order = $message->getOrder();
            /* @var $order Epicor_Comm_Model_Order */

            $helper = $this->commSalesOrderHelper;
            /* @var $helper Epicor_Comm_Helper_Sales_Order */

            if (!$order->getEmailSent() && ($order->getEccErpOrderNumber() || $helper->showWebOrderNumberOnly($order))) {
                try {
                    $this->orderSender->send($order);
                    $paymentMethod = $order->getPayment()->getMethod();
                    /**
                     * Order Approved send invoice email
                     * when method is epicor payment.
                     */
                    $actionName = $this->request->getActionName();
                    if ($order->getIsApprovalPending() != 1 && $paymentMethod == 'pay' && !$this->strposa($this->request->getOriginalPathInfo(), ['payment-information'])) {
                        $invoice = current($order->getInvoiceCollection()->getItems());
                        if ($invoice) {
                            $this->invoiceSender->send($invoice);
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }

    /**
     * @param $haystack
     * @param $needle
     * @param int $offset
     * @return bool
     */
    private function strposa($haystack, $needle, $offset = 0)
    {
        if (!is_array($needle)) {
            $needle = array($needle);
        }
        foreach ($needle as $query) {
            if (strpos($haystack, $query, $offset) !== false) {
                return true;
            } // stop on first true result
        }
        return false;
    }

}