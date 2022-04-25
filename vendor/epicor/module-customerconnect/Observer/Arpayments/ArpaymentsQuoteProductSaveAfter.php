<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

class ArpaymentsQuoteProductSaveAfter extends AbstractObserver  implements \Magento\Framework\Event\ObserverInterface
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
        $handle     = $this->arpaymentsHelper->checkArpaymentsPage();
        $quote_item = $observer->getEvent()->getQuoteItem();
        $quote      = $quote_item->getQuote();
        if ($quote->getArpaymentsQuote()) {
            $arQuote = $this->arpaymentsHelper->getArpaymentsQuote();
            $price   = $arQuote->getGrandTotal(); //set your price here
            $quote->setCustomerId(NULL);
            $quote->setGrandTotal($arQuote->getGrandTotal());
            $quote->setBaseGrandTotal($arQuote->getGrandTotal());
            $quote_item->setCustomPrice($arQuote->getGrandTotal());
            $quote_item->setPrice($arQuote->getGrandTotal());
            $quote_item->setBasePrice($arQuote->getGrandTotal());
            $quote_item->setRowTotal($arQuote->getGrandTotal());
            $quote_item->setBaseRowTotal($arQuote->getGrandTotal());
            $quote_item->setPriceIncTax($arQuote->getGrandTotal());
            $quote_item->setBasePriceIncTax($arQuote->getGrandTotal());
            $quote_item->setRowTotalIncTax($arQuote->getGrandTotal());
            $quote_item->setBaseRowTotalIncTax($arQuote->getGrandTotal());
            $quote_item->setOriginalCustomPrice($price);
            $quote_item->getProduct()->setIsSuperMode(true);
        }
    }
}