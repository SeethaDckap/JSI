<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Block\Checkout\Cart;


class Totals extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var \Magento\Msrp\Model\Config
     */
    protected $msrpConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Msrp\Model\Config $msrpConfig,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->msrpConfig = $msrpConfig;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $salesConfig,
            $layoutProcessors,
            $data
        );
    }

    /**
     * Check if can apply msrp to totals
     *
     * @return bool
     */
    public function canApplyMsrp()
    {
        if (!$this->getQuote()->hasCanApplyMsrp() && $this->msrpConfig->isEnabled()) {
            $this->getQuote()->collectTotals();
        }
        return $this->getQuote()->getCanApplyMsrp();
    }
}