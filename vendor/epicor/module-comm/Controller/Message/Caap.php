<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Message;

class Caap extends \Epicor\Comm\Controller\Message
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

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Customerconnect\Model\ArPayment\OrderFactory $salesOrderFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Model\ArPayment\QuoteFactory $quoteRepository
    )
    {
        $this->salesOrderFactory = $salesOrderFactory;
        $this->registry = $registry;
        $this->eventManager = $context->getEventManager();
        $this->quoteRepository = $quoteRepository;
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

            if (!$this->registry->registry("offline_arpaymentorders_{$order->getId()}")) {
                $this->registry->register("offline_arpaymentorders_{$order->getId()}", true);
            }

            $quotes = $this->quoteRepository->create();
            $quote  = $quotes->load($order->getQuoteId());
            $this->eventManager->dispatch('ar_checkout_submit_all_after', array(
                'quote' => $quote,
                'order' => $order,
            ));
            $this->registry->unregister("offline_arpaymentorders_{$order->getId()}");
        }
    }

}
