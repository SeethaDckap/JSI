<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Crqd\Information;

class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{

    protected $messageTypes ="CRQD";

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
            $this->addOption('quote_number', __('quoteNumber'));
            $this->addOption('quote_date', __('quoteDate'));
            $this->addOption('taxid', __('taxid'));
            $this->addOption('quote_status', __('quoteStatus'));
            $this->addOption('payment_terms', __('paymentTerms'));
            $this->addOption('fob', __('fob'));
            $this->addOption('delivery_method', __('deliveryMethod'));
            $this->addOption('web_reference', __('webReference'));
            $this->addOption('quote_sequence', __('quoteSequence'));
            $this->addOption('case_number', __('caseNumber'));
            $this->addOption('required_date', __('requiredDate'));
            $this->addOption('customer_reference', __('customerReference'));
            $this->addOption('sales_rep_id', __('salesRepId'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('recurring_quote', __('recurringQuote'));
            $this->addOption('contract_code', __('contractCode'));
            $this->addOption('quote_entered', __('quoteEntered'));
        }
        return parent::_toHtml();
    }

}