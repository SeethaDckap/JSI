<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Plugin\Order;


class OrderViewAuthorizationPlugin
{


    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $salesOrderConfig;
    

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $salesOrderConfig
    ) {
        $this->customerSession = $customerSession;
        $this->salesOrderConfig = $salesOrderConfig;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function aroundCanView(
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $subject,
        \Closure $proceed,
        \Magento\Sales\Model\Order $order
    ) {
        $result = $proceed($order);
        $customer = $this->customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        if ($customer->isSalesRep()) {
            $customerId = $customer->getId();

            $availableStates = array_keys($this->salesOrderConfig->getStates());
            if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId || $order->getEccSalesrepCustomerId() == $customerId) && in_array($order->getState(), $availableStates, $strict = true)
            ) {
                return true;
            }

            return false;
        } else {
            return $result;
        }
    }
    
    
}