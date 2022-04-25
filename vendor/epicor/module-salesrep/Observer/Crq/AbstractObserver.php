<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Crq;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $salesRepHelper;

    protected $customerSession;

    protected $request;

    protected $registry;

    protected $customerconnectRfqHelper;

    protected $salesRepCrqsDetailsLinesRendererCurrencyFactory;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper,
        \Epicor\SalesRep\Block\Crqs\Details\Lines\Renderer\CurrencyFactory $salesRepCrqsDetailsLinesRendererCurrencyFactory,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper
    ) {
        $this->salesRepCrqsDetailsLinesRendererCurrencyFactory = $salesRepCrqsDetailsLinesRendererCurrencyFactory;
        $this->salesRepHelper = $salesRepHelper;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->registry = $registry;
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
    }


}

