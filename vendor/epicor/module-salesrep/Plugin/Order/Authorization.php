<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\SalesRep\Plugin\Order;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;

class Authorization extends \Magento\Sales\Model\ResourceModel\Order\Plugin\Authorization
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;


    /**
     * @param UserContextInterface $userContext
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->userContext = $userContext;
        $this->customerSession = $customerSession;
         parent::__construct($userContext);
    }


    /**
     * @param ResourceOrder $subject
     * @param ResourceOrder $result
     * @param \Magento\Framework\Model\AbstractModel $order
     * @return ResourceOrder
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(
        ResourceOrder $subject,
        ResourceOrder $result,
        \Magento\Framework\Model\AbstractModel $order
    ) {
        if ($order instanceof Order) {
            if (!$this->isAllowed($order)) {
                throw NoSuchEntityException::singleField('orderId', $order->getId());
            }
        }
        return $result;
    }
    /**
     * Checks if order is allowed for current customer
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function isAllowed(\Magento\Sales\Model\Order $order)
    {
        $result = parent::isAllowed($order);
        $customer = $this->customerSession->getCustomer();
        if ($customer->isSalesRep()) {
            $customerId = $customer->getId();
            if ($order->getCustomerId() == $customerId || $order->getEccSalesrepCustomerId() == $customerId
            ) {
                return TRUE;
            }

            return FALSE;
        }
        return $result;
    }
}
