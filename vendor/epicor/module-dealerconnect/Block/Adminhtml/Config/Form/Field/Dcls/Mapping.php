<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Dcls;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="DCLS";

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
            $this->addOption('case_number', __('caseNumber'));
            $this->addOption('identification_number', __('identificationNumber'));
            $this->addOption('create_on_date', __('createOnDate'));
            $this->addOption('description', __('description'));
            $this->addOption('customer_code', __('customerCode'));
            $this->addOption('customer_name', __('customerName'));
            $this->addOption('product_code', __('productCode'));
            $this->addOption('serial_numbers_serial_number', __('serialNumbers'));
            $this->addOption('status', __('status'));
            $this->addOption('order_number', __('orderNumber'));
            $this->addOption('invoice_number', __('invoiceNumber'));
            $this->addOption('erpReturns_number', __('erpReturnsNumber'));
            $this->addOption('quote_number', __('quoteNumber'));
        }
        return parent::_toHtml();
    }

}
