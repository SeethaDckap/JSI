<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Observer;

//class UpdateQuoteStatusToOrdered extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
class UpdateQuoteStatusToOrdered implements \Magento\Framework\Event\ObserverInterface
{
    

    protected $checkoutSession;

    protected $registry;

    protected $salesOrderFactory;

    protected $quotesQuoteFactory;

    protected $quotesHelper;
    
    protected $responseFactory;
    
    protected $url;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\Quotes\Helper\Data $quotesHelper,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url           
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->quotesHelper = $quotesHelper;
        $this->responseFactory = $responseFactory;
        $this->url = $url;        
    }
    
 
    /**
     * @var \Epicor\Quotes\Helper\Data
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order_ids = $observer->getEvent()->getOrderIds();
        if (count($order_ids) > 0) { 
            $order = $this->salesOrderFactory->create()->load($order_ids[0]); //echo $order->getEccQuoteId(); die('ssdd');
            if ($order->getEccQuoteId()) {
                $quote = $this->quotesQuoteFactory->create()->load($order->getEccQuoteId());
                /* @var $quote Epicor_Quotes_Model_Quote */
                $quote->setStatusId(\Epicor\Quotes\Model\Quote::STATUS_QUOTE_ORDERED);
                $quote->save();
            }
            if($order->getArpaymentsQuote()) {
                $redirectionUrl = $this->url->getUrl('customerconnect/arpayments/redirectpage');
                $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                return $this;                
            }
        }
    }

}