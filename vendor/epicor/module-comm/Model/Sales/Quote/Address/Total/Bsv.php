<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Sales\Quote\Address\Total;


class Bsv extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Epicor\Comm\Model\Message\Request\BsvFactory
     */
    protected $commMessageRequestBsvFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Epicor\Comm\Model\Message\Request\BsvFactory $commMessageRequestBsvFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper
    )
    {
        $this->request = $request;
        $this->registry = $registry;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->commMessageRequestBsvFactory = $commMessageRequestBsvFactory;
        $this->scopeConfig = $scopeConfig;
        $this->directoryHelper = $directoryHelper;
        $this->setCode('bsv');
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     * @return array
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);
        $address = $this->_getAddress();
        if ($address->getAddressType() != 'shipping') {
            return $this;
        }
        $_fromCurr = $quote->getBaseCurrencyCode() ?: $quote->getStore()->getBaseCurrencyCode();
        $_toCurr = $quote->getStore()->getCurrentCurrencyCode();

        $this->_cleanEmptyQuote($quote, $address);
        $this->_bsvOverrideItemTotals($address);
        $this->_bsvOverrideTotals($address, $address, $_fromCurr, $_toCurr);
        $this->_bsvOverrideTotals($total, $address, $_fromCurr, $_toCurr);
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [];
    }

    /**
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     */
    private function _bsvOverrideTotals($object, $address, $_fromCurr, $_toCurr)
    {

        $convertor = $this->directoryHelper;
        /* @var $convertor \Magento\Directory\Helper\Data */

        $bsvGoodsTotal = $address->getEccBsvGoodsTotal();
        $bsvGoodsTotalInc = $address->getEccBsvGoodsTotalInc();
        $bsvCarriageAmount = $address->getEccBsvCarriageAmount();
        $bsvCarriageAmountInc = $address->getEccBsvCarriageAmountInc();
        $bsvGrandTotal = $address->getEccBsvGrandTotal();
        $bsvGrandTotalInc = $address->getEccBsvGrandTotalInc();

        if (!is_null($bsvGoodsTotal)) {
            $object->setBaseSubtotal($bsvGoodsTotal);
            $object->setBaseTotalAmount('subtotal', $bsvGoodsTotal);

            $object->setSubtotal($convertor->currencyConvert($bsvGoodsTotal, $_fromCurr, $_toCurr));
            $object->setTotalAmount('subtotal', $convertor->currencyConvert($bsvGoodsTotal, $_fromCurr, $_toCurr));
        }

        if (!is_null($bsvGoodsTotalInc)) {
            $object->setBaseSubtotalInclTax($bsvGoodsTotalInc);
            $object->setBaseSubtotalTotalInclTax($bsvGoodsTotalInc);
            $object->setSubtotalInclTax($convertor->currencyConvert($bsvGoodsTotalInc, $_fromCurr, $_toCurr));
        }


        //https://github.com/magento/magento2/issues/16388
        //https://github.com/magento/magento2/issues/14206
        //There are lot of issues in this area. We fixed only for flatrate.
        //we need to follow the same approach, if any issue was raised for someother method
        $registries = $this->registry->registry('flat_rate_applied_zero');

        if ((!is_null($bsvCarriageAmount)) && ($registries !="0")) {
            $object->setBaseShippingAmount($bsvCarriageAmount);
            $object->setShippingAmount($convertor->currencyConvert($bsvCarriageAmount, $_fromCurr, $_toCurr));
        }

        if ((!is_null($bsvCarriageAmountInc)) && ($registries !="0")) {
            $object->setBaseShippingInclTax($bsvCarriageAmountInc);
            $object->setShippingInclTax($convertor->currencyConvert($bsvCarriageAmountInc, $_fromCurr, $_toCurr));
        }

        if ($object instanceof \Magento\Quote\Model\Quote\Address\Total) {
            $this->_bsvOverrideTax($address,$object);
        }

        if (!is_null($bsvGrandTotalInc)) {
            $object->setBaseGrandTotal($bsvGrandTotalInc);
            $object->setBaseTotalAmount('grand', $bsvGrandTotalInc);

            $object->setGrandTotal($convertor->currencyConvert($bsvGrandTotalInc, $_fromCurr, $_toCurr));
            $object->setTotalAmount('grand', $convertor->currencyConvert($bsvGrandTotalInc, $_fromCurr, $_toCurr));
        }
    }

    /**
     *
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     */
    private function _cleanEmptyQuote($quote, $address)
    {
        if (count($address->getAllItems()) == 0) {

            $data = [
                'ecc_bsv_goods_total' => null,
                'ecc_bsv_goods_total_inc' => null,
                'ecc_bsv_carriage_amount' => null,
                'ecc_bsv_carriage_amount_inc' => null,
                'ecc_bsv_discount_amount' => null,
                'ecc_bsv_grand_total' => null,
                'ecc_bsv_grand_total_inc' => null,
                'base_subtotal' => 0,
                'subtotal' => 0,
                'base_subtotal_incl_tax' => 0,
                'base_subtotal_total_incl_tax' => 0,
                'subtotal_incl_tax' => 0,
                'base_shipping_amount' => 0,
                'shipping_amount' => 0,
                'base_shipping_incl_tax' => 0,
                'shipping_incl_tax' => 0,
                'base_grand_total' => 0,
                'grand_total' => 0
            ];

            $address->setBaseTotalAmount('subtotal', 0);
            $address->setBaseTotalAmount('grand', 0);
            $address->setTotalAmount('subtotal', 0);
            $address->setTotalAmount('grand', 0);

            $address->addData($data);
            $quote->addData($data);
        }
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     *
     */
    private function _bsvOverrideTax($address,$total)
    {
        $convertor = $this->directoryHelper;
        /* @var $convertor \Magento\Directory\Helper\Data */
        $_fromCurr = $address->getQuote()->getBaseCurrencyCode() ?: $address->getQuote()->getStore()->getBaseCurrencyCode();
        $_toCurr = $address->getQuote()->getStore()->getCurrentCurrencyCode();

        $bsvGoodsTotal = $address->getEccBsvGoodsTotal();
        $bsvGoodsTotalInc = $address->getEccBsvGoodsTotalInc();
        $bsvCarriageAmount = $address->getEccBsvCarriageAmount();
        $bsvCarriageAmountInc = $address->getEccBsvCarriageAmountInc();
        $bsvGrandTotal = $address->getEccBsvGrandTotal();
        $bsvGrandTotalInc = $address->getEccBsvGrandTotalInc();

        $appliedTaxes = array();

        $registries = $this->registry->registry('flat_rate_applied_zero');

        if ((!is_null($bsvCarriageAmount)) && ($registries !="0")) {
            $total->setBaseShippingAmount($bsvCarriageAmount);
            $total->setShippingAmount($convertor->currencyConvert($bsvCarriageAmount, $_fromCurr, $_toCurr));
        }

        if ((!is_null($bsvCarriageAmountInc)) && ($registries !="0")) {
            $total->setBaseShippingInclTax($bsvCarriageAmountInc);
            $total->setShippingInclTax($convertor->currencyConvert($bsvCarriageAmountInc, $_fromCurr, $_toCurr));
            $shippingtax = $bsvCarriageAmountInc - $bsvCarriageAmount;
            $total->setBaseShippingTaxAmount($shippingtax);
            $total->setShippingTaxAmount($shippingtax);
        }

        if (!is_null($bsvGoodsTotal) && !is_null($bsvGoodsTotalInc)) {
            $goodsTax = $bsvGoodsTotalInc - $bsvGoodsTotal;
            $appliedTaxes['goodstax'] = array(
                'rates' => array(array(
                    'code' => __('Goods Tax'),
                    'title' => __('Goods Tax'),
                    'percent' => null,
                    'position' => 1,
                    'priority' => 1,
                    'rule_id' => 1
                )),
                'percent' => null,
                'id' => 'goodstax',
                'process' => 0,
                'amount' => $convertor->currencyConvert($goodsTax, $_fromCurr, $_toCurr),
                'base_amount' => $goodsTax
            );
        }

        if (!is_null($bsvCarriageAmount) && !is_null($bsvCarriageAmountInc)) {
            $carriageTax = $bsvCarriageAmountInc - $bsvCarriageAmount;
            $appliedTaxes['carriagetax'] = array(
                'rates' => array(array(
                    'code' => __('Carriage Tax'),
                    'title' => __('Carriage Tax'),
                    'percent' => null,
                    'position' => 2,
                    'priority' => 2,
                    'rule_id' => 2
                )),
                'percent' => null,
                'id' => 'carriagetax',
                'process' => 0,
                'amount' => $convertor->currencyConvert($carriageTax, $_fromCurr, $_toCurr),
                'base_amount' => $carriageTax
            );
        }

        if (!is_null($bsvGrandTotal) && !is_null($bsvGrandTotalInc)) {
            $tax = $bsvGrandTotalInc - $bsvGrandTotal;

            $total->setBaseTaxAmount($tax);
            $total->setBaseTotalAmount('tax', $tax);

            $total->setTaxAmount($convertor->currencyConvert($tax, $_fromCurr, $_toCurr));
            $total->setTotalAmount('tax', $convertor->currencyConvert($tax, $_fromCurr, $_toCurr));

            $area = null;
            if ($this->scopeConfig->isSetFlag('tax/cart_display/grandtotal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $address->getGrandTotal()) {
                $area = 'taxes';
            }

            $address->addTotal(array(
                'code' => 'tax',
                'title' => __('Tax'),
                'full_info' => $appliedTaxes,
                'value' => $tax,
                'area' => $area
            ));
        }

        $address->setAppliedTaxes($appliedTaxes);
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     *
     */
    private function _bsvOverrideItemTotals($address)
    {
        $this->_setAddress($address);
        $items = $address->getQuote()->getAllItems();

        foreach ($items as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if ($item->getParentItem()) {
                continue;
            }

            $children = $item->getChildren();
            if ($children) {
                foreach ($children as $child) {
                    /* @var $child \Magento\Quote\Model\Quote\Item */
                    $this->_setBsvItemValues($child);
                }
            }
            $this->_setBsvItemValues($item);
        }

        $quote = $address->getQuote();

        if ($quote->getIsMultiShipping()) {
            foreach ($address->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                $children = $item->getChildren();
                if ($children) {
                    foreach ($children as $child) {
                        $this->_setBsvItemValues($child);
                    }
                } else {
                    $this->_setBsvItemValues($item);
                }
            }
        }

        return $this;
    }

    /**
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    private function _setBsvItemValues(&$item)
    {
        $convertor = $this->directoryHelper;
        /* @var $convertor \Magento\Directory\Helper\Data */

        $_fromCurr = $item->getQuote()->getBaseCurrencyCode() ?: $item->getQuote()->getStore()->getBaseCurrencyCode();
        $_toCurr = $item->getQuote()->getStore()->getCurrentCurrencyCode();

        if (!is_null($item->getEccBsvPrice())) {
            $item->setConvertedPrice(null);
            $item->setBasePrice($item->getEccBsvPrice());
            $item->setPrice($item->getEccBsvPrice());
            $item->setOriginalPrice($item->getEccBsvPrice());
            $item->setBaseOriginalPrice($item->getEccBsvPrice());
            $cartPrice = $convertor->currencyConvert($item->getEccBsvPrice(), $_fromCurr, $_toCurr);
            $item->setOriginalCustomPrice($cartPrice);
            $item->setCustomPrice($cartPrice);
        }

        if (!is_null($item->getEccBsvPriceInc())) {
            $item->setBasePriceInclTax($item->getEccBsvPriceInc());
            $item->setPriceInclTax($convertor->currencyConvert($item->getEccBsvPriceInc(), $_fromCurr, $_toCurr));
        }

        if (!is_null($item->getEccBsvLineValue())) {
            $item->setBaseRowTotal($item->getEccBsvLineValue());
            $item->setRowTotal($convertor->currencyConvert($item->getEccBsvLineValue(), $_fromCurr, $_toCurr));
        }

        if (!is_null($item->getEccBsvLineValueInc())) {
            $item->setBaseRowTotalInclTax($item->getEccBsvLineValueInc());
            $item->setRowTotalInclTax($convertor->currencyConvert($item->getEccBsvLineValueInc(), $_fromCurr, $_toCurr));

            $baseTax = $item->getBaseRowTotalInclTax() - $item->getBaseRowTotal();
            $tax = $item->getRowTotalInclTax() - $item->getRowTotal();

            $item->setBaseTaxAmount($baseTax);
            $item->setTaxAmount($convertor->currencyConvert($tax, $_fromCurr, $_toCurr));
        }
    }
}
