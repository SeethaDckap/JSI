<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

class ArpaymentsOrderPushFromPaymentSave  implements \Magento\Framework\Event\ObserverInterface
{
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $_request;
    protected $arpaymentsHelper;
    
    public function __construct(\Magento\Framework\Registry $registry, 
                                \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper, 
                                \Magento\Framework\App\Request\Http $request)
    {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_registry        = $registry;
        $this->_request         = $request;
    }
    
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        $order  = $observer->getEvent()->getOrder();
        if ($handle || $order->getArpaymentsQuote()) {
            if($order->getPayment()->getMethod()=="paypal_express") {
                $getPaymentMethod = $this->arpaymentsHelper->setPaymentMethod($order, $order->getPayment()->getMethod());
            }
        }
    }
}