<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Cuod\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{
    protected $messageTypes = "CUOD";

    protected $gridMappingHelper;

    protected $_gridMessageSection;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Common\Helper\GridMapping $gridMappingHelper,
        array $data = []
    )
    {
        $this->gridMappingHelper = $gridMappingHelper;
        parent::__construct(
            $context,
            $gridMappingHelper,
            $data
        );
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function setColumnName($value)
    {
        //M1 > M2 Translation Begin (Rule 22)
        //return $this->setExtraParams('rel="#{index}" style="width:200px"');
        return $this->setExtraParams('rel="<%- index %>" style="width:50%"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('order_date', __('orderDate'));
            $this->addOption('required_date', __('requiredDate'));
            $this->addOption('payment_terms', __('paymentTerms'));
            $this->addOption('customer_reference', __('customerReference'));
            $this->addOption('salesRep > name', __('salesRep > name'));
            $this->addOption('delivery_method', __('deliveryMethod'));
            $this->addOption('fob', __('fob'));
            $this->addOption('taxid', __('taxid'));
            $this->addOption('contract_code', __('contractCode'));
            $this->addOption('additional_reference', __('additionalReference'));
            $this->addOption('ship_status', __('shipStatus'));
            $this->addOption('salesRep > number', __('salesRep > number'));
            $this->addOption('reseller > name', __('reseller > name'));
            $this->addOption('reseller > number', __('reseller > number'));
            $this->addOption('orderTaker > name', __('orderTaker > name'));
            $this->addOption('orderTaker > number', __('orderTaker > number'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('order_status', __('orderStatus'));
            $this->addOption('tax_exempt_reference', __('taxExemptReference'));
        }
        return parent::_toHtml();
    }

}
