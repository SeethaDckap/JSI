<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Orders\Details\Lines\Renderer;

/**
 * CRQ line currency column renderer
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Currency extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;
    protected $dealerHelper;
    protected $customerSession;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Magento\Framework\Registry $registry, \Epicor\Comm\Helper\Messaging $commMessagingHelper, \Epicor\Dealerconnect\Helper\Data $dealerHelper, \Magento\Customer\Model\Session $customerSession, array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->dealerHelper = $dealerHelper;
        $this->customerSession = $customerSession;
        parent::__construct(
                $context, $registry, $commMessagingHelper, $data
        );
    }

    public function render(\Magento\Framework\DataObject $row) {
        $order = $this->registry->registry('customer_connect_order_details');
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $dealerHelper = $this->dealerHelper;
        /* @var $helper Epicor_Dealerconnect_Helper_Data */
        $index = $this->getColumn()->getIndex();
        $currentMode = $this->customerSession->getDealerCurrentMode();
        $canShowMargin = $dealerHelper->checkCustomerMarginAllowed();
        $canShowCusPrice = $dealerHelper->checkCustomerCusPriceAllowed();
        $currency = $helper->getCurrencyMapping($order->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        $price = $helper->formatPrice($row->getData($index), true, $currency);
        $discount = $helper->formatPrice($row->getData('dealer_line_discount')/$row->getData('quantity_ordered'), true, $currency);
        $resetDisStyle = $currentMode === 'shopper' ? ' style="display:none"' : '';
        $resetMargStyle = ($currentMode === 'dealer' && $canShowMargin !== 'disable') ? '' : ' style="display:none"';

        if ($row->getData($index)) {

            if ($index === 'price') {
                if ($row->getData('dealer_price_inc') < 1) {
                    $margin = (($row->getData('dealer_price_inc') - $row->getData('price')) / ($row->getData('dealer_price_inc') + 0.000000000001)) * 100;
                } else {
                    $margin = ($row->getData('dealer_price_inc') - $row->getData('price')) / $row->getData('dealer_price_inc') * 100;
                }
                $formatMargin = $helper->formatPrice($margin, true, $currency);
                $html = '<div class="buyPrice">' . $helper->formatPrice($row->getData($index), true, $currency) . '<br><span class ="baseMargin"' . $resetMargStyle . '><b>' . number_format($margin, 2, '.', '') . '%</b></span></div>';
            } else {
                $html = '<div class="dealPrice"><span>' . $price . '</span><br><span class="dealerDisc" ' . $resetDisStyle . '><b>' . $discount . '</b></span></div>';
            }
        } else {
            if ($index === 'price') {
                $html = '<span>' . $price . '</span>';
            } else {
                $html = '<div class="dealPrice"><span>' . $price . '</span><br><span class="dealerDisc" ' . $resetDisStyle . '><b>' . $discount . '</b></span></div>';
            }
        }
        return $html;
    }

}
