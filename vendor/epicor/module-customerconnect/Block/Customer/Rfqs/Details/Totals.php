<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ line totals display
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Totals extends \Epicor\Common\Block\Generic\Totals
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_rfqs_misc';

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
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory $resourceErpMappingMiscCollectionFactory,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commHelper = $commHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->resourceErpMappingMiscCollectionFactory = $resourceErpMappingMiscCollectionFactory;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $commonHelper,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();

        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/totals.phtml');

        $rfq = $this->registry->registry('customer_connect_rfq_details');

        $columns = 10;

        if ($rfq) {

            $helper = $this->commMessagingHelper;

            $options = array(//'display' => ''
            );

            $currencyCode = $helper->getCurrencyMapping(
                $rfq->getCurrencyCode(), \Epicor\Customerconnect\Helper\Data::ERP_TO_MAGENTO
            );
            $lines = ($rfq->getLines()) ? $rfq->getLines()->getasarrayLine() : array();
            if (!empty($lines)) {
                $callback = function($misc) {
                    return $misc->getMiscellaneousChargesTotal();
                };
                $lines = array_map($callback, $lines);
                $miscSubtotal = array_sum($lines) + $rfq->getGoodsTotal();
            }
            if($rfq->getMiscellaneousChargesTotal() && $this->canShowMisc()){
                $this->addRow(
                    'Subtotal :', $helper->getCurrencyConvertedAmount($miscSubtotal, $currencyCode, null, $options), 'subtotal'
                );
                $carriageAmt = $rfq->getMiscellaneousChargesTotal();
                $expandDef = $this->customerconnectHelper->checkCusMiscView();
                $filteredMiscArr = $this->getFileteredMisc($rfq->getMiscellaneousCharges());
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
                $this->addRow(
                    'Subtotal :', $helper->getCurrencyConvertedAmount($rfq->getGoodsTotal(), $currencyCode, null, $options), 'subtotal'
                );
                $this->addRow(
                    'Shipping  &amp; Handling :', $helper->getCurrencyConvertedAmount($rfq->getCarriageAmount(), $currencyCode, null, $options), 'shipping'
                );
            }

            if ($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != 'e10') {

                if (!$this->commHelper->removeTaxLine($rfq->getTaxAmount())) {     // only display tax line if config value is set
                    $this->addRow(
                        'Tax :', $helper->getCurrencyConvertedAmount($rfq->getTaxAmount(), $currencyCode, null, $options), 'tax'
                    );
                }
            }

            $this->addRow(
                'Grand Total :', $helper->getCurrencyConvertedAmount($rfq->getGrandTotal(), $currencyCode, null, $options), 'grand_total'
            );
        }

        if ($this->registry->registry('rfqs_editable')) {
            $this->setColumns($columns);
        } else {
            $this->setColumns($columns - 1);
        }
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
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ);
        return $showMiscCharges && $isMiscAllowed;
    }
}
