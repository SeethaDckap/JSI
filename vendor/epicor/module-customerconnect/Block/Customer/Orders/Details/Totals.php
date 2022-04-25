<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Orders\Details;


class Totals extends \Epicor\Common\Block\Generic\Totals
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_orders_misc';
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

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    private $contract;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory $resourceErpMappingMiscCollectionFactory,
        \Epicor\Lists\Helper\Frontend\Contract $contract,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->commHelper = $commHelper;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerconnectHelper = $customerconnectHelper;
        $this->resourceErpMappingMiscCollectionFactory = $resourceErpMappingMiscCollectionFactory;
        $this->_accessauthorization = $context->getAccessAuthorization();
        $this->contract = $contract;
        parent::__construct(
            $context,
            $commonHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $order = $this->registry->registry('customer_connect_order_details');
        $locHelper = $this->commLocationsHelper;
        $showLoc = ($locHelper->isLocationsEnabled()) ? $locHelper->showIn('cc_orders') : false;
        $columns = 10;
        if (!$showLoc) {
            $columns = 9;
        }
        if ($this->contract->contractsDisabled()) {
            $columns = $columns - 1;
        }

        if ($order) {

            $helper = $this->commMessagingHelper;
            $currencyCode = $helper->getCurrencyMapping($order->getCurrencyCode(), \Epicor\Customerconnect\Helper\Data::ERP_TO_MAGENTO);
            $lines = ($order->getLines()) ? $order->getLines()->getasarrayLine() : array();
            if (!empty($lines)) {
                $callback = function($misc) {
                    return $misc->getMiscellaneousChargesTotal();
                };
                $lines = array_map($callback, $lines);
                $miscSubtotal = array_sum($lines) + $order->getGoodsTotal();
            }

            if ($order->getMiscellaneousChargesTotal() && $this->canShowMisc()){
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount($miscSubtotal, $currencyCode), 'subtotal');
                $carriageAmt = $order->getMiscellaneousChargesTotal();
                $expandDef = $this->customerconnectHelper->checkCusMiscView();
                $filteredMiscArr = $this->getFileteredMisc($order->getMiscellaneousCharges());
                if(empty($filteredMiscArr)){
                    $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode), 'shipping');
                }else{
                    $miscTotal = array_sum(array_column($filteredMiscArr, 'line_value'));
                    $this->addRow('Miscellaneous Charges:', $helper->getCurrencyConvertedAmount($miscTotal, $currencyCode), 'misc', '', 0, 0, true, $expandDef);
                    $this->addSubRow('misc', $filteredMiscArr);
                    $carriageAmt = $carriageAmt  - $miscTotal;
                    if($carriageAmt > 0){
                        $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($carriageAmt, $currencyCode), 'shipping');
                    }
                }
                $columns++;
            }else{
                $this->addRow('Subtotal :', $helper->getCurrencyConvertedAmount($order->getGoodsTotal(), $currencyCode), 'subtotal');
                $this->addRow('Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($order->getCarriageAmount(), $currencyCode), 'shipping');
            }

            if (!$this->commHelper->removeTaxLine($order->getTaxAmount())) {
                $this->addRow('Tax :', $helper->getCurrencyConvertedAmount($order->getTaxAmount(), $currencyCode));
            }

            $this->addRow('Grand Total :', $helper->getCurrencyConvertedAmount($order->getGrandTotal(), $currencyCode), 'grand_total');
        }

        if ($this->scopeConfig->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $columns++;
        }
        $this->setColumns($columns - 1);
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
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ);
        return $showMiscCharges && $isMiscAllowed;
    }

}
