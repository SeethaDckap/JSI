<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Multishipping;


class Success extends \Magento\Multishipping\Block\Checkout\Success
{

    protected $_orders = array();

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        array $data = [],
        \Magento\Sales\Model\OrderFactory $salesOrderFactory
    )
    {
        $this->salesOrderFactory = $salesOrderFactory;
        parent::__construct($context, $multishipping, $data);
    }

    public function getErpOrderNumber($orderId)
    {
        if (!isset($this->_orders[$orderId])) {
            $order = $this->salesOrderFactory->create()->load($orderId);
            $this->_orders[$orderId] = $order->getEccErpOrderNumber();
        }

        return $this->_orders[$orderId];
    }

}
