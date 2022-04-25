<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

use Epicor\Customerconnect\Helper\Arpayments;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class ArpaymentsOrderPush  implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var Http
     */
    protected $_request;

    /**
     * @var Arpayments
     */
    protected $arpaymentsHelper;

    /**
     * ArpaymentsOrderPush constructor.
     * @param Registry $registry
     * @param Arpayments $arpaymentsHelper
     * @param Http $request
     */
    public function __construct(
        Registry $registry,
        Arpayments $arpaymentsHelper,
        Http $request
    ) {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->_registry        = $registry;
        $this->_request         = $request;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $handle = $this->arpaymentsHelper->checkArpaymentsPage();
        $order  = $observer->getEvent()->getOrder();
        if ($order && ($handle || $order->getArpaymentsQuote())) {
            $this->arpaymentsHelper->setPaymentMethod(
                $order,
                $order->getPayment()->getMethod(),
                $observer->getQuote()->getPayment()->getEccElementsTransactionId()
            );
        }
    }
}
