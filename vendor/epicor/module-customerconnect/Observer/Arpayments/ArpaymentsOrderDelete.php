<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

class ArpaymentsOrderDelete extends AbstractObserver  implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $_request;
    protected $arpaymentsHelper;
    protected $salesOrderFactory;
    protected $arSession;


    public function __construct(\Magento\Framework\Registry $registry,
                                \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
                                \Magento\Sales\Model\OrderFactory $salesOrderFactory,
                                \Epicor\Customerconnect\Model\ArPayment\Session\Proxy $arSession,
                                \Magento\Framework\App\Request\Http $request)
    {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_registry        = $registry;
        $this->_request         = $request;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->arSession = $arSession;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $realOrderId = $this->arSession->getRealArOrderId();
        if(!empty($realOrderId)) {
            $order = $this->salesOrderFactory->create()->load($realOrderId);
            if ($order->getArpaymentsQuote()) {
                $this->arpaymentsHelper->deleteOrder($order);
                $this->arpaymentsHelper->deleteRecord($order->getId());
            }
        }
    }
}