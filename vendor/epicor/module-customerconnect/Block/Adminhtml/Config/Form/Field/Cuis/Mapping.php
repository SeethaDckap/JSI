<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Cuis;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{

    protected $messageTypes ="CUIS";

    protected $gridMappingHelper;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Common\Helper\GridMapping $gridMappingHelper,
        array $data = []
    ) {
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

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- index %>" style="width:200px"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('_attributes_type', __('type'));
            $this->addOption('invoice_number', __('invoiceNumber'));
            $this->addOption('invoice_date', __('invoiceDate'));
            $this->addOption('due_date', __('dueDate'));
            $this->addOption('our_order_number', __('ourOrderNumber'));
            $this->addOption('customer_reference', __('customerReference'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('original_value', __('originalValue'));
            $this->addOption('payment_value', __('paymentValue'));
            $this->addOption('outstanding_value', __('outstandingValue'));
            $this->addOption('invoice_status', __('invoiceStatus'));
            $this->addOption('contracts_contract_code', __('contractCode'));
            $this->addOption('central_collection', __('centralCollection'));
        }
        return parent::_toHtml();
    }

}
