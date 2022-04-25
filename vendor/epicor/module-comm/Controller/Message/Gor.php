<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Message;

class Gor extends \Epicor\Comm\Controller\Message
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Sales\Model\OrderFactory $salesOrderFactory,
    \Magento\Framework\Registry $registry
    ) {
        $this->salesOrderFactory = $salesOrderFactory;
        $this->registry = $registry;
        $this->eventManager = $context->getEventManager();
        parent::__construct(
            $context
        );
    }

    public function execute()
    {

        $orderID = $this->getRequest()->getParam('id');
        $this->salesOrderFactory->create()->load($orderID);
        $order = $this->salesOrderFactory->create()->load($orderID);

        if (!$order->isObjectNew()) {

            if (!$this->registry->registry("offline_order_{$order->getId()}")) {
                $this->registry->register("offline_order_{$order->getId()}", true);
            }

            $this->eventManager->dispatch('sales_order_save_commit_after', array(
                'data_object' => $order,
                'order' => $order,
            ));
            $this->registry->unregister("offline_order_{$order->getId()}");
        }
    }

}
