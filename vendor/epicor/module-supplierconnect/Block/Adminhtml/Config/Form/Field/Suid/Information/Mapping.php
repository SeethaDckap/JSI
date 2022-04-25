<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Suid\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{
    protected $messageTypes = "SUID";

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
        return $this->setExtraParams('rel="<%- index %>" style="width:50%"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('invoice_date', __('invoiceDate'));
            $this->addOption('payment_terms', __('paymentTerms'));
            $this->addOption('due_date', __('dueDate'));
            $this->addOption('invoice_status', __('invoiceStatus'));
            $this->addOption('invoice_number', __('invoiceNumber'));
            $this->addOption('discount_date', __('discountDate'));
            $this->addOption('currency_code', __('currencyCode'));
        }
        return parent::_toHtml();
    }

}
