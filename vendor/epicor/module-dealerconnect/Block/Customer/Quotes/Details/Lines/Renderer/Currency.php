<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines\Renderer;

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

    protected $_customPartColumns = [
        'price',
        'dealer_price_inc'
    ];

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
        $precision = $this->customerconnectHelper->getProductPricePrecision();
        $index = $this->getColumn()->getIndex();
        $currentMode = $this->customerSession->getDealerCurrentMode();
        $canToggle = $this->dealerHelper->checkCustomerToggleAllowed();
        $canShowMargin = $this->dealerHelper->checkCustomerMarginAllowed();
        $canShowCusPrice = $this->dealerHelper->checkCustomerCusPriceAllowed();
        $helper = $this->commMessagingHelper;
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        $currency = $helper->getCurrencyMapping($rfq->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        $changedByErp = 'N';
        if (!is_null($row->getData('dealer')) && !is_null($row->getData('dealer')->getData('_attributes'))) {
            $changedByErp = $row->getData('dealer')->getData('_attributes')->getChangedByErp();
        }
        if ($index === 'line_value') {
            if ($currentMode == "shopper") {
                $price = $row->getData('dealer_line_value_inc');
                if ($changedByErp == 'Y') {
                    $price = $row->getData('dealer_price') * $row->getData('quantity');
                    if ($row->getData('dealer_price') == 0 && $row->getData('dealer_base_price') != 0) {
                        $price = $row->getData('dealer_base_price') * $row->getData('quantity');
                    } else if($row->getData('dealer_base_price') == 0){
                        $price = $row->getData($index);
                    }
                }

            } else {
                $price = $row->getData($index);
            }
            $price = is_null($price) ? "0" : $price;
            if($this->canShowMisc()){
                $price += $row->getData('miscellaneous_charges_total');
                $html = '<input type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . ($row->getData($index) + $row->getData('miscellaneous_charges_total')) . '" class="lines_' . $index . '"/>';
            }else{
                $html = '<input type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $row->getData($index) . '" class="lines_' . $index . '"/>';
            }

            if ($price == 'TBC' || $price == '') {
                $html .= '<span class="lines_' . $index . '_display">' . $price . '</span>';
            } else {
                $html .= '<span class="lines_' . $index . '_display">' . $helper->formatPrice($price, true, $currency) . '</span>';
            }

            return $html;
        }
        $rowProduct = $this->customerconnectMessagingHelper->getProductObject((string) $row->getData('product_code'));
        /* @var $rowProduct \Epicor\Comm\Model\Product */

        if ($this->registry->registry('rfqs_editable') && ($rowProduct instanceof \Epicor\Comm\Model\Product && !$rowProduct->isObjectNew())) {
            $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
            $rfq = $this->registry->registry('customer_connect_rfq_details');
            /* @var $helper \Epicor\Comm\Helper\Messaging */
            $currency = $helper->getCurrencyMapping($rfq->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
            $currencySymbol = $helper->getCurrencySymbol($currency);
            $uniqueId = $row->getUniqueId();
            $html = parent::render($row);
            $controller = $this->request->getControllerName();
            $module = $this->request->getModuleName();
            $action = $this->request->getActionName();
            $dealerPrice = number_format($row->getData('dealer_price_inc'), $precision, '.', '');
            $cusPrice = number_format($row->getData('price'), $precision, '.', '');
            if ($changedByErp == 'Y' && $dealerPrice == 0) {
                $dealerPrice = $row->getData('dealer_base_price');
                $dealerPrice = ($dealerPrice == 0) ? $row->getData('price') : $dealerPrice;
                $dealerPrice = number_format($dealerPrice, $precision, '.', '');
            }
            $discountAmount = number_format($row->getData('dealer_line_discount') / $row->getData('quantity'), $precision, '.', '');
            if ($changedByErp == 'Y' && $row->getData('dealer_price') != 0) {
                $discountAmount = $row->getData('dealer_base_price') - $row->getData('dealer_price');
                $discountAmount = number_format($discountAmount, $precision, '.', '');
            }
            $origAmount = number_format($row->getData('dealer_base_price'), $precision, '.', '');
            $discountOrig = number_format(0, $precision, '.', '');
            $isDealer = $currentMode === "shopper" ? 1 : 0;
            $basePrice = $origAmount;
            if ($dealerPrice == 0 || is_null($dealerPrice)) {
                $margin = 0;
            } else {
                $margin = number_format(((($dealerPrice - $cusPrice) / $dealerPrice) * 100), $precision, '.', '');
            }
            $resetStyle = ($basePrice == $dealerPrice) ? 'style="display:none"' : '';
            $resetMargin = ($canShowMargin === "disable") ? 'style="display:none"' : '';
            $resetLink = '<div id="reset_discount_' . $uniqueId . '" ' . $resetStyle . ' ><a class="reset_discount_line_level" href="javascript:dealerPricing.resetDiscount(\'' . $uniqueId . '\')">' . __('Revert to Web Price') . '</a></div>';
            $togglePriceLink = '<div  id="toggle_price_' . $uniqueId . '" style = "display:none" class = "toggle_price" onclick = "javascript:dealerPricing.togglePrice(\'' . $uniqueId . '\')"></div>';
            if ($index === 'price') {
                $html = '<div class="customer-container" id="cus-cart-item-' . $uniqueId . '">'
                        . '<span class="discount-currency left">' . $currencySymbol . '</span>'
                        . '<input readonly type="text" customer-cartid="' . $uniqueId . '" customer-type="price" name="lines[' . $key . '][' . $uniqueId . '][' . $index . ']" value="' . $cusPrice . '" size="12" title="' . __('Price') . '" class="input-text price lines_base_price no_update disabled" maxlength="20" style="width:86px;" />'
                        . '<div ' . $resetMargin . '>' . '<span class="left">' . __('Margin ') . '<span>%</span></span>' . '<input readonly type="text" customer-cartid="' . $uniqueId . '" customer-type="margin" name="lines[' . $key . '][' . $uniqueId . '][customer-discount]" value="' . $margin . '" size="4" title="' . __('Discount') . '" class="input-text customer-margin disabled" maxlength="12" /></div>'
                        . '<span class="lines_price_display" style="display:none"></span></div>';
            } else {
                $html = '<div class="dealer-container" id="cart-item-' . $uniqueId . '">'
                        . '<span class="discount-currency left">' . $currencySymbol . '</span>'
                        . '<input type="text" dealer-cartid="' . $uniqueId . '" dealer-type="price" name="lines[' . $key . '][' . $uniqueId . '][' . $index . ']" orig-value="' . $origAmount . '" cus-price="' . $cusPrice . '" dealer-price="' . $dealerPrice . '" dealer="' . $isDealer . '" value="' . $dealerPrice . '" size="12" title="' . __('Price') . '" class="input-text price lines_price no_update disabled" maxlength="20" style="width:86px;float:left;" />'
                        . '<input class="dp_base_price" type="hidden" value="' . $basePrice . '" name="lines[' . $key . '][' . $uniqueId . '][dp_base_price]"/>'
                        . '<div>' . '<span class="left">' . __('Discount') . '</span>' . '<input type="text" dealer-cartid="' . $uniqueId . '" dealer-type="discount" name="lines[' . $key . '][' . $uniqueId . '][dealer-discount]" orig-value="' . $discountOrig . '" value="' . $discountAmount . '" size="4" title="' . __('Discount') . '" class="input-text dealer-discount disabled" maxlength="12" style="width:86px !important;float:left;" /></div>'
                        . $resetLink
                        . $togglePriceLink
                        . '<span class="lines_price_display" style="display:none"></span></div>';
            }
            return $html;
        }
        if (in_array($index, $this->_customPartColumns) && !($rowProduct instanceof \Epicor\Comm\Model\Product)) {
            $price = ($currentMode === "shopper" && $changedByErp != 'Y') ? $row->getData('dealer_price_inc') : $row->getData('price');
            $price = is_null($price) ? "0" : $price;
            $uniqueId = $row->getUniqueId();
            $togglePriceLink = '<div  id="toggle_price_' . $uniqueId . '" style = "display:none" class = "toggle_price" onclick = "javascript:dealerPricing.togglePrice(\'' . $uniqueId . '\')"></div>';
            $html = '<input type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $price . '" class="lines_' . $index . '"/>';
            if ($price == 'TBC' || $price == '') {
                $html .= '<span class="lines_' . $index . '_display">' . $price . '</span>';
            } else {
                $html .= '<span class="lines_' . $index . '_display">' . $helper->formatPrice($price, true, $currency) . '</span>';
            }
            $html .= $index === 'price' ? $togglePriceLink : '';
            return $html;
        }
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ_DEALER);
        return $showMiscCharges && $isMiscAllowed;
    }
}
