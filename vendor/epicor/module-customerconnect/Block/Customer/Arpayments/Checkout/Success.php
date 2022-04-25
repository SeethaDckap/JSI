<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Checkout;

use Magento\Customer\Model\Context;
use Epicor\Customerconnect\Model\ArPayment\Order;

class Success extends \Magento\Framework\View\Element\Template
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
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;    
    
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;    

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutSession,
        \Epicor\Customerconnect\Model\ArPayment\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Epicor\Customerconnect\Model\ArPayment\OrderFactory $salesOrderFactory,
        array $data = []
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->_orderConfig = $orderConfig;        
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Initialize data and prepare it for output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }    

    protected function prepareBlockData()
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        if ($orderId) {
            $order = $this->salesOrderFactory->create()->load($orderId);
            $this->addData(
                [
                    'is_order_visible' => $this->isVisible($order),
                    'erp_order_number' => $order->getErpArpaymentsOrderNumber(),
                    'view_order_url' => $this->getUrl(
                        'customerconnect/arpayments/view/',
                        ['order_id' => $order->getEntityId()]
                    ),
                    'print_url' => $this->getUrl(
                        'customerconnect/arpayments/printpayment/',
                        ['order_id' => $order->getEntityId()]
                    ),
                    'can_print_order' => $this->isVisible($order),
                    'can_view_order'  => $this->canViewOrder($order),
                    'order_id'  => $order->getIncrementId()
                ]
            );            
        }
    }
    
    /**
     * Is order visible
     *
     * @param Order $order
     * @return bool
     */
    protected function isVisible(Order $order)
    {
        return !in_array(
            $order->getStatus(),
            $this->_orderConfig->getInvisibleOnFrontStatuses()
        );
    }
    
    /**
     * Can view order
     *
     * @param Order $order
     * @return bool
     */
    protected function canViewOrder(Order $order)
    {
        return true;
    }    
    

    public function getErpArpaymentsOrderNumber()
    {
        return $this->_getData('erp_order_number');
    }

}
