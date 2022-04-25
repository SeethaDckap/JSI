<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Sups;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="SUPS";

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
            $this->addOption('payment_date', __('paymentDate'));
            $this->addOption('payment_reference', __('paymentReference'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('payment_amount', __('paymentAmount'));
            $this->addOption('invoice_number', __('invoiceNumber'));
            $this->addOption('invoice_payment_amount', __('invoicePaymentAmount'));
        }
        return parent::_toHtml();
    }

}
