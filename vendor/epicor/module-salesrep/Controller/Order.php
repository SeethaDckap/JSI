<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller;


//M1 > M2 Translation Begin (Rule P2-5.7)
//require_once Mage::getModuleDir('controllers', 'Mage_Sales') . '/' . 'OrderController.php';

//M1 > M2 Translation End


abstract class Order extends \Magento\Framework\App\Action\Action
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
     * Check order view availability
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  bool
     */
    protected function _canViewOrder($order)
    {
        $customer = $this->customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        if ($customer->isSalesRep()) {
            $customerId = $customer->getId();

            $availableStates = $this->salesOrderConfig->getVisibleOnFrontStates();

            if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId || $order->getEccSalesrepCustomerId() == $customerId) && in_array($order->getState(), $availableStates, $strict = true)
            ) {
                return true;
            }

            return false;
        } else {
            return parent::_canViewOrder($order);
        }
    }

}
