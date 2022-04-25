<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Cuid\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{
    protected $messageTypes = "CUID";

    protected $gridMappingHelper;

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
        return $this->setExtraParams('rel="<%- index %>" style="width:50%"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('_attributes_type', __('type'));
            $this->addOption('invoice_number', __('invoiceNumber'));
            $this->addOption('date', __('date'));
            $this->addOption('due_date', __('dueDate'));
            $this->addOption('our_order_number', __('ourOrderNumber'));
            $this->addOption('customer_reference', __('customerReference'));
            $this->addOption('legal_number', __('legalNumber'));
            $this->addOption('salesRep > name', __('salesRep > name'));
            $this->addOption('salesRep > number', __('salesRep > number'));
            $this->addOption('orderTaker > name', __('orderTaker > name'));
            $this->addOption('orderTaker > number', __('orderTaker > number'));
            $this->addOption('reseller > number', __('reseller > number'));
            $this->addOption('reseller > name', __('reseller > name'));
            $this->addOption('delivery_method', __('deliveryMethod'));
            $this->addOption('payment_terms', __('paymentTerms'));
            $this->addOption('order_status', __('orderStatus'));
            $this->addOption('fob', __('fob'));
            $this->addOption('depot_number', __('depotNumber'));
            $this->addOption('contract_code', __('contractCode'));
            $this->addOption('tax_exempt_reference', __('taxExemptReference'));
            $this->addOption('central_collection', __('centralCollection'));
        }
        return parent::_toHtml();
    }
}
