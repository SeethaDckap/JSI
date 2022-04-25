<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Spos;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="SPOS";

    protected $gridMappingHelper;

    protected $_gridMessageSection;

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
        if (strpos($value, 'newpogrid_config') !== false) {
            $this->_gridMessageSection = "newpogrid_config";
        }
        return $this->setName($value);
    }

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- index %>" style="width:200px"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('purchase_order_number', __('purchaseOrderNumber'));
            $this->addOption('order_date', __('orderDate'));
            $this->addOption('order_status', __('orderStatus'));
            $this->addOption('order_confirmed', __('orderConfirmed'));
            $this->addOption('delivery_address', __('deliveryAddress'));
            $this->addOption('delivery_address_street', __('deliveryAddress > street'));
            $this->addOption('delivery_address_address_code', __('deliveryAddress > addressCode'));
            $this->addOption('delivery_address_name', __('deliveryAddress > name'));
            $this->addOption('delivery_address_address1', __('deliveryAddress > address1'));
            $this->addOption('delivery_address_address2', __('deliveryAddress > address2'));
            $this->addOption('delivery_address_address3', __('deliveryAddress > address3'));
            $this->addOption('delivery_address_city', __('deliveryAddress > city'));
            $this->addOption('delivery_address_county', __('deliveryAddress > county'));
            $this->addOption('delivery_address_country', __('deliveryAddress > country'));
            $this->addOption('delivery_address_postcode', __('deliveryAddress > postcode'));
            $this->addOption('delivery_address_telephone_number', __('deliveryAddress > telephoneNumber'));
            $this->addOption('delivery_address_fax_number', __('deliveryAddress > faxNumber'));
            $this->addOption('delivery_address_carriage_text', __('deliveryAddress > carriageText'));
            $this->addOption('new_po_confirm', __('New PO Confirm'));
            $this->addOption('new_po_reject', __('New PO Reject'));
        }
        return parent::_toHtml();
    }

}
