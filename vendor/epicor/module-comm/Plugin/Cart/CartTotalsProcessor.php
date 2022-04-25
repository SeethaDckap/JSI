<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Cart;


class CartTotalsProcessor
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commonContext;

    /**
     * @var \Epicor\Comm\Helper\Context
     */
    protected $commHelper;

    /**
     * CartTotalsProcessor checkout Cart.
     *
     * @param \Epicor\Common\Helper\Context $commonContext
     */
    public function __construct(
        \Epicor\Common\Helper\Context $commonContext
    )
    {
        $this->commHelper = $commonContext->getCommHelper();
    }

    /**
     * AfterProcess checkout Cart.
     *
     * @param \Magento\Checkout\Block\Cart\CartTotalsProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Cart\CartTotalsProcessor $subject,
        array $jsLayout
    )
    {
        //check if Hide Prices are enabled
        $eccHidePrice = $this->commHelper->getEccHidePrice();
        if ($eccHidePrice) {
            $jsLayout['components']['block-totals']['children']['subtotal']['config']['template'] = "Epicor_Comm/cart/totals/subtotal";
            $jsLayout['components']['block-totals']['children']['shipping']['config']['template'] = "Epicor_Comm/cart/totals/shipping";
            $jsLayout['components']['block-totals']['children']['grand-total']['config']['template'] = "Epicor_Comm/cart/totals/grand-total";
        }
        return $jsLayout;
    }
}