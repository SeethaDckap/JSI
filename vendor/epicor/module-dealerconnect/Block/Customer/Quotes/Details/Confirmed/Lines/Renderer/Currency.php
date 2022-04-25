<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details\Confirmed\Lines\Renderer;

/**
 * CRQ line currency column renderer
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Currency extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency {

    const FRONTEND_RESOURCE_INFORMATION_READ_DEALER = 'Dealer_Connect::dealer_quotes_misc';
    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;
    protected $request;
    protected $customerSession;
    protected $dealerHelper;
    protected $customerconnectHelper;
    protected $_accessauthorization;

    public function __construct(
    \Magento\Backend\Block\Context $context, \Magento\Framework\Registry $registry, \Epicor\Comm\Helper\Messaging $commMessagingHelper, \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper, \Magento\Framework\App\Request\Http $request, \Magento\Customer\Model\Session $customerSession, \Epicor\Dealerconnect\Helper\Data $dealerHelper, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, array $data = []
    ) {
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->dealerHelper = $dealerHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
                $context, $registry, $commMessagingHelper, $data
        );
    }

    public function render(\Magento\Framework\DataObject $row) {

        $rfq = $this->registry->registry('customer_connect_rfq_details');
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        $dealerHelper = $this->dealerHelper;
        /* @var $helper Epicor_Dealerconnect_Helper_Data */
        $index = $this->getColumn()->getIndex();
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $currentMode = $this->customerSession->getDealerCurrentMode();
        $canShowMargin = $dealerHelper->checkCustomerMarginAllowed();
        $canShowCusPrice = $dealerHelper->checkCustomerCusPriceAllowed();
        $currency = $helper->getCurrencyMapping($rfq->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        $price = $helper->formatPrice($row->getData($index), true, $currency);
        $discount = $helper->formatPrice($row->getData('dealer_line_discount')/$row->getData('quantity'), true, $currency);
        $resetDisStyle = $currentMode === 'shopper' ? ' style="display:none"' : '';
        $resetMargStyle = ($currentMode === 'dealer' && $canShowMargin !== 'disable') ? '' : ' style="display:none"';

        if ($row->getData($index)) {
            if ($index === 'line_value') {
                $dealerPrice = $row->getData('dealer_line_value_inc');
                $cusPrice = $row->getData($index);
                $price = $currentMode === "shopper" ? $dealerPrice : $cusPrice;
                if($this->canShowMisc()){
                    $price += $row->getData('miscellaneous_charges_total');
                }
                $html = '<input dealerPrice ="' . $dealerPrice . '" cusPrice = "' . $cusPrice . '"type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $row->getData($index) . '" class="lines_' . $index . '"/>';
                if ($price == 'TBC' || $price == '') {
                    $html .= '<span class="lines_' . $index . '_display">' . $price . '</span>';
                } else {
                    $html .= '<span class="lines_' . $index . '_display">' . $helper->formatPrice($price, true, $currency) . '</span>';
                }
                
            }else if ($index === 'price') {
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

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ_DEALER);
        return $showMiscCharges && $isMiscAllowed;
    }

}
