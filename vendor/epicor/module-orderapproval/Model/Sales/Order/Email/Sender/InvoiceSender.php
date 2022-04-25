<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\OrderApproval\Model\Sales\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;


class InvoiceSender
    extends \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
{
    /**
     * InvoiceSender constructor.
     *
     * @param Template                                           $templateContainer
     * @param InvoiceIdentity                                    $identityContainer
     * @param Order\Email\SenderBuilderFactory                   $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param Renderer                                           $addressRenderer
     * @param PaymentHelper                                      $paymentHelper
     * @param InvoiceResource                                    $invoiceResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface                                   $eventManager
     */
    public function __construct(
        Template $templateContainer,
        InvoiceIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        InvoiceResource $invoiceResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $paymentHelper,
            $invoiceResource,
            $globalConfig,
            $eventManager
        );
    }

    /**
     * Sends order invoice email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Invoice $invoice
     * @param bool    $forceSyncMode
     *
     * @return bool
     * @throws \Exception
     */
    public function send(Invoice $invoice, $forceSyncMode = false)
    {
        $order = $invoice->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();
        if ($order->getIsApprovalPending() == 1 && $paymentMethod == 'pay') {
            return false;
        }

        parent::send($invoice, $forceSyncMode);
    }
}
