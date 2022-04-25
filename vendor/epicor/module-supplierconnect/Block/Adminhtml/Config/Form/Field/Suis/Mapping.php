<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Suis;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="SUIS";

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
            $this->addOption('invoice_number', __('invoiceNumber'));
            $this->addOption('invoice_date', __('invoiceDate'));
            $this->addOption('due_date', __('dueDate'));
            $this->addOption('purchase_order_number', __('purchaseOrderNumber'));
            $this->addOption('supplier_reference', __('supplierReference'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('goods_total', __('goodsTotal'));
            $this->addOption('tax_amount', __('taxAmount'));
            $this->addOption('grand_total', __('grandTotal'));
            $this->addOption('balance_due', __('balanceDue'));
            $this->addOption('invoice_status', __('invoiceStatus'));
        }
        return parent::_toHtml();
    }

}
