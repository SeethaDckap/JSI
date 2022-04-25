<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Cre\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderInterface;

class ConvertQuotePayment implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Epicor\Elements\Logger\Logger $logger
     */
    protected $logger;


    /**
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
       
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        if ($order->getPayment()->getMethod() == 'cre') {
            $payment = $order->getPayment();
            $paymentQuotes = $quote->getPayment();
            $payment->setEccCcvToken($paymentQuotes->getEccCcvToken());
            $payment->setEccCvvToken($paymentQuotes->getEccCvvToken());
            $payment->setCcLast4($paymentQuotes->getCcLast4());
            $payment->setCreTransactionId($paymentQuotes->getCreTransactionId());
            $payment->setCcTransId($paymentQuotes->getCreTransactionId());
            $payment->setCcExpMonth($paymentQuotes->getCcExpMonth());
            $payment->setCcExpYear($paymentQuotes->getCcExpYear());            
        }
    }


}
