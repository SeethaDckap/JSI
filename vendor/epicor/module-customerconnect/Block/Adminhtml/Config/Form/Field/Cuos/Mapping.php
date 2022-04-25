<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Cuos;

class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{

    protected $messageTypes ="CUOS";

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
            $this->addOption('order_number', __('orderNumber'));
            $this->addOption('order_date', __('orderDate'));
            $this->addOption('customer_reference', __('customerReference'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('original_value', __('originalValue'));
            $this->addOption('order_status', __('orderStatus'));
            $this->addOption('order_address', __('orderAddress'));
            $this->addOption('order_address_street', __('orderAddress > street'));
            $this->addOption('order_address_customer_code', __('orderAddress > customerCode'));
            $this->addOption('order_address_name', __('orderAddress > name'));
            $this->addOption('order_address_address1', __('orderAddress > address1'));
            $this->addOption('order_address_address2', __('orderAddress > address2'));
            $this->addOption('order_address_address3', __('orderAddress > address3'));
            $this->addOption('order_address_city', __('orderAddress > city'));
            $this->addOption('order_address_country', __('orderAddress > country'));
            $this->addOption('order_address_postcode', __('orderAddress > postcode'));
            $this->addOption('order_address_telephone_number', __('orderAddress > telephoneNumber'));
            $this->addOption('order_address_fax_number', __('orderAddress > faxNumber'));
            $this->addOption('contracts_contract_code', __('contract > contractCode'));
            $this->addOption('dealer_grand_total_inc', __('dealer > grandTotalInc'));
            $this->addOption('additional_reference', __('Additional Reference'));
            $this->addOption('required_date', __('Required date'));
        }
        return parent::_toHtml();
    }

}
