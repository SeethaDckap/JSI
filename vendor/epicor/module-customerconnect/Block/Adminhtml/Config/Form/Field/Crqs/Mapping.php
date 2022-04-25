<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Crqs;



class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{

    protected $messageTypes ="CRQS";

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
            $this->addOption('quote_number', __('quoteNumber'));
            $this->addOption('quote_sequence', __('quoteSequence'));
            $this->addOption('quote_date', __('quoteDate'));
            $this->addOption('due_date', __('dueDate'));
            $this->addOption('description', __('description'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('original_value', __('originalValue'));
            $this->addOption('customer_reference', __('customerReference'));
            $this->addOption('quote_status', __('quoteStatus'));
            $this->addOption('quote_entered', __('quoteEntered'));
            $this->addOption('quote_delivery_address', __('quoteDeliveryAddress'));
            $this->addOption('quote_delivery_address_customer_code', __('quoteDeliveryAddress > customerCode'));
            $this->addOption('quote_delivery_address_name', __('quoteDeliveryAddress > name'));
            $this->addOption('quote_delivery_address_address1', __('quoteDeliveryAddress > address1'));
            $this->addOption('quote_delivery_address_address2', __('quoteDeliveryAddress > address2'));
            $this->addOption('quote_delivery_address_address3', __('quoteDeliveryAddress > address3'));
            $this->addOption('quote_delivery_address_city', __('quoteDeliveryAddress > city'));
            $this->addOption('quote_delivery_address_county', __('quoteDeliveryAddress > county'));
            $this->addOption('quote_delivery_address_country', __('quoteDeliveryAddress > country'));
            $this->addOption('quote_delivery_address_postcode', __('quoteDeliveryAddress > postcode'));
            $this->addOption('quote_delivery_address_telephone_number', __('quoteDeliveryAddress > telephoneNumber'));
            $this->addOption('quote_delivery_address_fax_number', __('quoteDeliveryAddress > faxNumber'));
            $this->addOption('contracts_contract_code', __('contractCode'));
            $this->addOption('dealer_grand_total_inc', __('dealer > grandTotalInc'));
        }
        return parent::_toHtml();
    }

}