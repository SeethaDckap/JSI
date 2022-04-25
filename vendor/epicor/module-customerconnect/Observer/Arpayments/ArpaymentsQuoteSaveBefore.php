<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer\Arpayments;

class ArpaymentsQuoteSaveBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
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
        $handle  = $this->arpaymentsHelper->checkArpaymentsPage();
        $quote   = $observer->getQuote();
        if ($quote->getArpaymentsQuote()) {
            $arQuote = $this->arpaymentsHelper->getArpaymentsQuote();
            foreach ($quote->getAllItems() as $arItems) {
                $arItems->setCustomPrice($arQuote->getGrandTotal());
                $arItems->setPrice($arQuote->getGrandTotal());
                $arItems->setBasePrice($arQuote->getGrandTotal());
                $arItems->setRowTotal($arQuote->getGrandTotal());
                $arItems->setBaseRowTotal($arQuote->getGrandTotal());
                $arItems->setPriceIncTax($arQuote->getGrandTotal());
                $arItems->setBasePriceIncTax($arQuote->getGrandTotal());
                $arItems->setRowTotalIncTax($arQuote->getGrandTotal());
                $arItems->setBaseRowTotalIncTax($arQuote->getGrandTotal());
                $arItems->setOriginalCustomPrice($arQuote->getGrandTotal());
                $arItems->getProduct()->setIsSuperMode(true);
                $arItems->save();
                $arItems->setQty(1);
                $arItems->setOriginalCustomPrice($arQuote->getGrandTotal());
            }
            $quote->setGrandTotal($arQuote->getGrandTotal());
            $quote->setBaseGrandTotal($arQuote->getGrandTotal());
            $quote->setCustomerId(null);
            $quote->setCustomerIsGuest(1);
            $quote->getBillingAddress()->setCustomerAddressId(null);
            $quote->getBillingAddress()->setShouldIgnoreValidation(true);
            $quote->getShippingAddress()->setCustomerAddressId(null);
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
        }
    }
}