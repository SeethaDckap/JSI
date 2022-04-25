<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Order\Helper;


use Magento\Sales\Model\Order;

/**
 * Class State
 */
class Reorder
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->customerSession = $customerSession;
    }
        
    public function aroundCanReorder(
        \Magento\Sales\Helper\Reorder $subject,
        \Closure $proceed,
        $orderId
    ) {
        $order = $this->orderFactory->create()->load($orderId);
        if (!$subject->isAllowed($order->getStore())) {
            return false;
        }
        if ($this->customerSession->isLoggedIn()) {
            return $order->canReorder();
        } else {
            return true;
        }
    }    
}
