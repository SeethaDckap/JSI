<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Onepage;

class Success extends \Magento\Checkout\Block\Onepage\Success
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        array $data = []
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $checkoutSession,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->setTemplate('epicor_comm/checkout/success.phtml');
    }

    protected function prepareBlockData()
    {
        parent::prepareBlockData();
        $orderId = $this->checkoutSession->getLastOrderId();
        if ($orderId) {
            $order = $this->salesOrderFactory->create()->load($orderId);
            /* @var $order Epicor_Comm_Model_Order */
            if ($order->getId()) {
                $this->addData(array(
                    'erp_order_number' => $order->getEccErpOrderNumber(),
                    'is_approval_pending' => $order->getIsApprovalPending()
                ));
            }
        }
    }

    public function getErpOrderNumber()
    {
        return $this->_getData('erp_order_number');
    }

    /**
     * Check approval is pending or not.
     *
     * @return boolean
     */
    public function getIsApprovalPending()
    {
        return $this->_getData('is_approval_pending');
    }

    /**
     * Approval message.
     *
     * @return string
     */
    public function getApprovalMessage()
    {
        if ($this->getIsApprovalPending() == 1) {
            $message = $this->scopeConfig->getValue(
                'ecc_order_approval/global/message_for_buyer',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ( ! $message) {
                $message = __("Your order is pending approval.");
            }

            return $message;
        }

        return "";
    }
}
