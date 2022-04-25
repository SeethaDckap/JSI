<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Orders\Details;

class Totals extends \Epicor\Common\Block\Generic\Totals {

    const FRONTEND_RESOURCE_INFORMATION_READ_DEALER = 'Dealer_Connect::dealer_orders_misc';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
    \Magento\Framework\View\Element\Template\Context $context, \Epicor\Common\Helper\Data $commonHelper, \Magento\Framework\Registry $registry, \Epicor\Comm\Helper\Messaging $commMessagingHelper, \Epicor\Comm\Helper\Data $commHelper, \Epicor\Comm\Helper\Locations $commLocationsHelper, \Magento\Customer\Model\Session $customerSession, \Epicor\Dealerconnect\Helper\Data $dealerHelper, \Epicor\Customerconnect\Helper\Data $customerconnectHelper,         \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory $resourceErpMappingMiscCollectionFactory,
    array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commHelper = $commHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->scopeConfig = $context->getScopeConfig();
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
        
        $order = $this->registry->registry('customer_connect_order_details');
        $currentMode = $this->customerSession->getDealerCurrentMode();
        $canShowCusPrice = $this->dealerHelper->checkCustomerCusPriceAllowed();
        if ($order) {
            $dealer = ($order->getDealer()) ? $order->getDealer() : array();
            $helper = $this->commMessagingHelper;
            $currencyCode = $helper->getCurrencyMapping($order->getCurrencyCode(), \Epicor\Customerconnect\Helper\Data::ERP_TO_MAGENTO);
            if ($currentMode === "dealer") {
                $subTot = $order->getGoodsTotal();
                $shipTot = $order->getCarriageAmount();
                $grandTot = $order->getGrandTotal();
                $tax = $order->getTaxAmount() ? $order->getTaxAmount() : 0 ;
            } else if ($currentMode === "shopper") {
                $subTot = !is_array($dealer) ? $dealer->getGrandTotalInc() : 0;
                $shipTot = !is_array($dealer) ? $dealer->getCarriageAmount() : 0;
                $grandTot = !is_array($dealer) ? $dealer->getGrandTotalInc() : 0;
                $tax = 0;
            }
            $lines = ($order->getLines()) ? $order->getLines()->getasarrayLine() : array();
            if (!empty($lines)) {
                $callback = function($misc) {
                    return $misc->getMiscellaneousChargesTotal();
                };
                $lines = array_map($callback, $lines);
                $miscSubtotal = array_sum($lines) + $subTot;
                $miscSubtotalD = (!is_array($dealer) ? $dealer->getGrandTotalInc() : 0) + array_sum($lines);
            }
            $dealerClass =  " no-display";
            $grandTot = ($this->canShowMisc()) ? $miscSubtotal + $order->getMiscellaneousChargesTotal() : $grandTot;
            $grandTotD = ($this->canShowMisc()) ? ($miscSubtotalD + $order->getMiscellaneousChargesTotal()) : (!is_array($dealer) ? $dealer->getGrandTotalInc() : 0);
            $grandTotO = $order->getGoodsTotal() + array_sum($lines) + $order->getMiscellaneousChargesTotal();
            if ($order->getMiscellaneousChargesTotal() && $this->canShowMisc()){
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount($miscSubtotal, $currencyCode), 'subtotal', '', 0, $helper->getCurrencyConvertedAmount($order->getGoodsTotal() + array_sum($lines), $currencyCode));
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount($miscSubtotalD, $currencyCode), 'dealer_subtotal' . $dealerClass);

                $carriageAmt = $order->getMiscellaneousChargesTotal();
                $expandDef = $this->customerconnectHelper->checkCusMiscView();
                $filteredMiscArr = $this->getFileteredMisc($order->getMiscellaneousCharges());
                if(empty($filteredMiscArr)){
                    $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode), 'shipping',  '', 0, $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode)
                    );
                    $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode), 'dealer_shipping' . $dealerClass);
                }else{
                    $miscTotal = array_sum(array_column($filteredMiscArr, 'line_value'));
                    $this->addRow('Miscellaneous Charges:', $helper->getCurrencyConvertedAmount($miscTotal, $currencyCode), 'misc', '', 0, 0, true, $expandDef);
                    $this->addSubRow('misc', $filteredMiscArr);
                    $carriageAmt = $carriageAmt  - $miscTotal;
                    if($carriageAmt > 0){
                        $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode), 'shipping',  '', 0, $helper->getCurrencyConvertedAmount($order->getCarriageAmount(), $currencyCode)
                        );
                        $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode), 'dealer_shipping' . $dealerClass);
                    }
                }

            }else{
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount($subTot, $currencyCode), 'subtotal', '', 0, $helper->getCurrencyConvertedAmount($order->getGoodsTotal(), $currencyCode));
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount(!is_array($dealer) ? $dealer->getGrandTotalInc() : 0, $currencyCode), 'dealer_subtotal' . $dealerClass);
                $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($shipTot, $currencyCode), 'shipping',  '', 0, $helper->getCurrencyConvertedAmount($order->getCarriageAmount(), $currencyCode));
                $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount(!is_array($dealer) ? $dealer->getCarriageAmount() : 0, $currencyCode), 'dealer_shipping' . $dealerClass);

            }
            if (!$this->commHelper->removeTaxLine($order->getTaxAmount())) {
                $this->addRow('Tax :', $helper->getCurrencyConvertedAmount($tax, $currencyCode), 'tax', '' ,0, $helper->getCurrencyConvertedAmount($tax, $currencyCode));
                $grandTot += $tax;
                $grandTotD += $tax;
                $grandTotO += $tax;
            }
            $this->addRow('Grand Total :', $helper->getCurrencyConvertedAmount($grandTot, $currencyCode), 'grand_total', '', 0, $helper->getCurrencyConvertedAmount($grandTotO, $currencyCode));
            $this->addRow('Grand Total :', $helper->getCurrencyConvertedAmount($grandTotD, $currencyCode), 'dealer_grand_total' . $dealerClass);
        }

        $locHelper = $this->commLocationsHelper;
        $showLoc = ($locHelper->isLocationsEnabled()) ? $locHelper->showIn('cc_orders') : false;
        $columns = 9;

        if (!$showLoc) {
            $columns = 8;
        }
        if ($currentMode === "dealer" && $canShowCusPrice !== "disable") {
            $columns++;
        }
        if ($this->scopeConfig->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $columns++;
        }

        $this->setColumns($columns);
    }

    public function isHidePricesActive()
    {
        return (bool) $this->commHelper->getEccHidePrice() && in_array($this->commHelper->getEccHidePrice(), [1, 2, 3]);
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
