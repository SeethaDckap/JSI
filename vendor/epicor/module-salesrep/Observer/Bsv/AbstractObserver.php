<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Bsv;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $salesRepHelper;

    protected $customerSession;

    protected $salesRepPricingRuleProductHelper;

    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->salesRepHelper = $salesRepHelper;
        $this->customerSession = $customerSession;
        $this->salesRepPricingRuleProductHelper = $salesRepPricingRuleProductHelper;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }

}

