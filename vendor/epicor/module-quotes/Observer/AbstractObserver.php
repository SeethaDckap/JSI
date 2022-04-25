<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Observer;


class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $checkoutSession;

    protected $registry;

    protected $salesOrderFactory;

    protected $quotesQuoteFactory;

    protected $quotesHelper;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\Quotes\Helper\Data $quotesHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->quotesHelper = $quotesHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }


}

