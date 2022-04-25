<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order\View\Addproducts\Renderer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Type
 *
 * @author Paul.Ketelle
 */
class Customprice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $_rowPrice;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->_localeResolver = $localeResolver;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $html = $this->getRowPrice($row, true);
        $html .= '<br /><input id="customprice_check_' . $row->getId() . '" title="Custom Price" type="checkbox" /><label for="customprice_check_' . $row->getId() . '">Custom Price</label>';
        $html .= '<br /><input style="width:60px;display:none;text-align:right;" type="text" class="custom_price" value="' . $this->getRowPrice($row) . '" id="customprice_' . $row->getId() . '" name="custom_price" value="' . $this->getRowPrice($row) . '" />';
        $html .= '<input type="hidden" value="' . $this->getRowPrice($row) . '" class="orig_price" id="origprice_' . $row->getId() . '" value="' . $this->getRowPrice($row) . '" />';
        return $html;
    }

    protected function getRowPrice(\Magento\Framework\DataObject $row, $show_currency = false)
    {


        $data = floatval($row->getPrice()) * $this->getColumn()->getRate();
        $this->_rowPrice = sprintf("%f", $data);

        if ($show_currency) {
            $currency_code = $this->getColumn()->getCurrencyCode();
            //M1 > M2 Translation Begin (Rule p2-6.4)
            //return Mage::app()->getLocale()->currency($currency_code)->toCurrency($this->_rowPrice);
            return $this->_localeResolver->getLocale()->currency($currency_code)->toCurrency($this->_rowPrice);
            //M1 > M2 Translation End
        } else
            return \Zend_Locale_Format::toNumber($this->_rowPrice, array('precision' => 2));
    }

}
