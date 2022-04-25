<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Approval\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Epicor\OrderApproval\Model\Approval\Email\Container\RejectedIdentity as OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\DataObject;

/**
 * Class RejectedSender
 *
 * @package Epicor\OrderApproval\Model\Approval\Email\Sender
 */
class RejectedSender extends Sender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * ApprovalSender constructor.
     *
     * @param Template                         $templateContainer
     * @param OrderIdentity                    $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface         $logger
     * @param Renderer                         $addressRenderer
     * @param PaymentHelper                    $paymentHelper
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer
        );
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Sends order approval email to the customer.
     *
     * @param Order $order
     * @param bool  $forceSyncMode
     *
     * @return bool
     */
    public function send(Order $order, $forceSyncMode = false)
    {
        if ($this->checkAndSend($order)) {
            return true;
        }

        return false;
    }

    /**
     * Prepare email template with variables
     *
     * @param Order $order
     *
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        $transport = [
            'order'                    => $order,
            'billing'                  => $order->getBillingAddress(),
            'payment_html'             => $this->getPaymentHtml($order),
            'store'                    => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress'  => $this->getFormattedBillingAddress($order),
            'created_at_formatted'     => $order->getCreatedAtFormatted(2),
            'order_data'               => [
                'customer_name'         => $order->getCustomerName(),
                'is_not_virtual'        => $order->getIsNotVirtual(),
                'email_customer_note'   => $order->getEmailCustomerNote(),
                'frontend_status_label' => $order->getFrontendStatusLabel(),
            ],
        ];
        $transportObject = new DataObject($transport);

        $this->templateContainer->setTemplateVars($transportObject->getData());

        parent::prepareTemplate($order);

        $this->templateContainer->setTemplateId($this->identityContainer->getTemplateId());
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     *
     * @return string
     */
    protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
}
