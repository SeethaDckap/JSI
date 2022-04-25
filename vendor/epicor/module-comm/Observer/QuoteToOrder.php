<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Observer;


class QuoteToOrder implements \Magento\Framework\Event\ObserverInterface
{
    private $objectCopyService;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->objectCopyService = $objectCopyService;
        $this->storeManager = $storeManager;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        //copy field in fieldset.xml
        $this->objectCopyService->copyFieldsetToTarget(
            'sales_convert_quote',
            'to_order',
            $quote,
            $order
        );
        
        $baseUrl = $this->storeManager->getStore($quote->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $quotePayment = $quote->getPayment();
        $orderPayment = $order->getPayment();
        $quotePayment->setEccSiteUrl($baseUrl);
        $orderPayment->setEccSiteUrl($quotePayment->getEccSiteUrl());
        $orderPayment->setEccIsSaved($quotePayment->getEccIsSaved());

        return $order;

    }

}