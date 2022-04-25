<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Config\Form\Field\Crrs;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="CRRS";

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
        return $this->setExtraParams('rel="<%- index %>" style="width:50px"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('erp_returns_number', __('erpReturnsNumber'));
            $this->addOption('web_returns_number', __('webReturnsNumber'));
            $this->addOption('rma_date', __('rmaDate'));
            $this->addOption('returns_status', __('returnsStatus'));
            $this->addOption('customer_reference', __('customerReference'));
            $this->addOption('customer_code', __('customerCode'));
            $this->addOption('customer_name', __('customerName'));
            $this->addOption('invoice_number', __('invoiceNumber'));
            $this->addOption('rma_case_number', __('rmaCaseNumber'));
            $this->addOption('rma_contact', __('rmaContact'));
        }
        return parent::_toHtml();
    }

}