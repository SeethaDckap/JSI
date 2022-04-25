<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details;

/**
 * RFQ line totals display
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Totals extends \Epicor\Common\Block\Generic\Totals {

    const FRONTEND_RESOURCE_INFORMATION_READ_DEALER = 'Dealer_Connect::dealer_quotes_misc';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory
     */
    protected $resourceErpMappingMiscCollectionFactory;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Epicor\Common\Helper\Data $commonHelper, \Magento\Framework\Registry $registry, \Epicor\Comm\Helper\Messaging $commMessagingHelper, \Epicor\Comm\Helper\Data $commHelper, \Magento\Customer\Model\Session $customerSession, \Epicor\Dealerconnect\Helper\Data $dealerHelper,  \Epicor\Customerconnect\Helper\Data $customerconnectHelper, \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory $resourceErpMappingMiscCollectionFactory, array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        $this->dealerHelper = $dealerHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->resourceErpMappingMiscCollectionFactory = $resourceErpMappingMiscCollectionFactory;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
                $context, $commonHelper, $data
        );
    }

    public function _construct() {
        parent::_construct();

        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/totals.phtml');
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        $currentMode = $this->customerSession->getDealerCurrentMode();
        $canShowCusPrice = $this->dealerHelper->checkCustomerCusPriceAllowed();
        $miscExtra = 0;
        if ($rfq) {
            $lines = ($rfq->getLines()) ? $rfq->getLines()->getasarrayLine() : array();
            if (!empty($lines)) {
                $callback = function($misc) {
                    return $misc->getMiscellaneousChargesTotal();
                };
                $lines = array_map($callback, $lines);
                $miscSubtotal = array_sum($lines);
                $miscExtra = ($rfq->getMiscellaneousChargesTotal() && $this->canShowMisc()) ? $miscSubtotal : 0;
                $miscShipping = ($rfq->getMiscellaneousChargesTotal() && $this->canShowMisc()) ? $rfq->getMiscellaneousChargesTotal() : 0;
            }
            $dealer = ($rfq->getDealer()) ? $rfq->getDealer() : array();
            $dealerGoodsTotal = !is_array($dealer) ? $dealer->getGoodsTotal() : 0;
            $dealerShippingAmt = !is_array($dealer) ? $dealer->getCarriageAmount() : 0;
            $dealerGrandTotal = !is_array($dealer) ? $dealer->getGrandTotal() + $miscExtra + $miscShipping : 0;
            $dealerGrandTotPost = !is_array($dealer) ? $dealer->getGrandTotal() : 0;
            $changedByErp = 'N';
            if (!is_array($dealer)) {
                if (!is_null($dealer) && !is_null($dealer->getData('_attributes'))) {
                    $changedByErp =  $dealer->getData('_attributes')->getChangedByErp();
                }
            }
            $helper = $this->commMessagingHelper;
            $options = array(//'display' => ''
            );

            $currencyCode = $helper->getCurrencyMapping(
                    $rfq->getCurrencyCode(), \Epicor\Customerconnect\Helper\Data::ERP_TO_MAGENTO
            );
            if ($currentMode == "shopper") {
                $dealerClass = "";
                $defaultClass = " no-display";
                $subTotal = $this->getSubTotal($rfq);
                $subTotal = ($changedByErp != 'Y' || $subTotal == 0) ? $dealerGoodsTotal : $subTotal;
                $subTotal = ($subTotal == 0) ? $rfq->getGoodsTotal() : $subTotal;
                if ($changedByErp == 'Y') {
                    $dealerGrandTotal = $subTotal + $dealerShippingAmt;
                }
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount(($subTotal + $miscExtra), $currencyCode, null, $options), 'dealer-subtotal' . $dealerClass, 'dealer-subtotal', ($dealerGoodsTotal));
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount(($rfq->getGoodsTotal() + $miscExtra), $currencyCode, null, $options), 'subtotal' . $defaultClass, 'subtotal', ($rfq->getGoodsTotal()));
            } else {
                $dealerClass = " no-display";
                $defaultClass = "";
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount(($rfq->getGoodsTotal() + $miscExtra), $currencyCode, null, $options), 'subtotal' . $defaultClass, 'subtotal', ($rfq->getGoodsTotal()));
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount(($dealerGoodsTotal + $miscExtra), $currencyCode, null, $options), 'dealer-subtotal' . $dealerClass, 'dealer-subtotal', ($dealerGoodsTotal));
            }
            if($rfq->getMiscellaneousChargesTotal() && $this->canShowMisc()){
                $carriageAmt = $rfq->getMiscellaneousChargesTotal();
                $expandDef = $this->customerconnectHelper->checkCusMiscView();
                $filteredMiscArr = $this->getFileteredMisc($rfq->getMiscellaneousCharges());
                if(empty($filteredMiscArr)){
                    $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode, null, $options), 'shipping' . $defaultClass, 'shipping', $rfq->getCarriageAmount());
                    $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode, null, $options), 'dealer-shipping' . $dealerClass, 'dealer-shipping', $dealerShippingAmt);
                }else{
                    $miscTotal = array_sum(array_column($filteredMiscArr, 'line_value'));
                    $this->addRow('Miscellaneous Charges:', $helper->getCurrencyConvertedAmount($miscTotal, $currencyCode), 'dealer-misc', '', 0, 0, true, $expandDef);
                    $this->addSubRow('dealer-misc', $filteredMiscArr);
                    $carriageAmt = $carriageAmt  - $miscTotal;
                    if($carriageAmt > 0){
                        $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode, null, $options), 'shipping' . $defaultClass, 'shipping', $rfq->getCarriageAmount());
                        $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode, null, $options), 'dealer-shipping' . $dealerClass, 'dealer-shipping', $dealerShippingAmt);
                    }
                }
            }else{
                $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($rfq->getCarriageAmount(), $currencyCode, null, $options), 'shipping' . $defaultClass, 'shipping', $rfq->getCarriageAmount());
                $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($dealerShippingAmt, $currencyCode, null, $options), 'dealer-shipping' . $dealerClass, 'dealer-shipping', $dealerShippingAmt);
            }

           if ($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != 'e10') {
                if (!$this->commHelper->removeTaxLine($rfq->getTaxAmount())) {     // only display tax line if config value is set
                    $this->addRow('Tax :', $helper->getCurrencyConvertedAmount($rfq->getTaxAmount(), $currencyCode, null, $options), 'tax' . $defaultClass);
                }
            }
            $this->addRow('Grand Total :', $helper->getCurrencyConvertedAmount($rfq->getGrandTotal(), $currencyCode, null, $options), 'grand_total' . $defaultClass, 'grand_total', $rfq->getGrandTotal());
            $this->addRow('Grand Total :', $helper->getCurrencyConvertedAmount($dealerGrandTotal, $currencyCode, null, $options), 'dealer-grand_total' . $dealerClass, 'dealer-grand_total', $dealerGrandTotPost);
        }

        if ($this->registry->registry('rfqs_editable')) {
            if ($currentMode === "dealer" && $canShowCusPrice !== "disable") {
                $this->setColumns(11);
            } else {
                $this->setColumns(10);
            }
        } else {
            if ($currentMode === "dealer" && $canShowCusPrice !== "disable") {
                $this->setColumns(10);
            } else {
                $this->setColumns(9);
            }
        }
    }

    /**
     *  Calculate the subtotal by looping through the line data if changed by ERP
     *
     * @param $rfq
     * @return float|int
     */
    protected function getSubTotal($rfq)
    {
        $endCustomerTotal = 0;
        if ($this->registry->registry('rfq_new')) {
            return $endCustomerTotal;
        }
        $_lines = ($rfq->getLines()) ? $rfq->getLines()->getasarrayLine() : [];
        foreach ($_lines as $_line) {
            $lineChangedByErp = 'N';
            $lineDealer = ($_line->getDealer()) ? $_line->getDealer() : array();
            if (!is_array($lineDealer)) {
                if (!is_null($lineDealer) && !is_null($lineDealer->getData('_attributes'))) {
                    $lineChangedByErp = 'Y';
                }
                if ($lineChangedByErp == 'Y') {
                    $lineDealerPrice = $lineDealer->getPrice();
                    if ($lineDealerPrice == 0) {
                        $lineDealerPrice = $lineDealer->getBasePrice();
                    }
                    $endCustomerTotal += ($_line->getQuantity() * $lineDealerPrice);
                } else {
                    $endCustomerTotal += ($_line->getQuantity() * $_line->getPrice());
                }

            }
        }
        return $endCustomerTotal;
    }

    public function HideTotalsBlock()
    {
        return $this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [1,2,3]);
    }

    public function  getFileteredMisc($miscCharges)
    {
        $miscArr = $miscCharges->getasarrayMiscellaneousLine();
        $callback = function($misc) {
            return $misc->getData();
        };
        $miscArr = array_map($callback, $miscArr);
        $miscCodeArr = array_column($miscArr, 'i_d');
        $collection = $this->resourceErpMappingMiscCollectionFactory->create();
        $colValues = $collection->addFieldToFilter('erp_code', array('in' => $miscCodeArr))->getColumnValues('erp_code');
        $arrDiff = array_diff($miscCodeArr, $colValues);
        $fileteredMiscArr = array_filter($miscArr, function($arrayValue) use($arrDiff) {
            return in_array($arrayValue['i_d'], $arrDiff);
        });
        return $fileteredMiscArr;
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ_DEALER);
        return $showMiscCharges && $isMiscAllowed;
    }
}
