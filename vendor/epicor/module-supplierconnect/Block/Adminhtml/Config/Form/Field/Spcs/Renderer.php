<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Spcs;


class Renderer extends \Magento\Framework\View\Element\Html\Select
{
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $data
        );
    }


    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- renderer %>" style="width:80px"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('Default'));
            $this->addOption(
                'Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Orderstatus', __('Order Status')
            );
            $this->addOption(
                'Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_Confirmed', __('Order Confirmed')
            );
            $this->addOption(
                'Epicor_Supplierconnect_Block_Customer_Orders_Create_Renderer_Linkpo', __('New PO Link')
            );
            $this->addOption(
                'Epicor_Supplierconnect_Block_Customer_Orders_Create_Renderer_Reject', __('Reject New PO')
            );
            $this->addOption(
                'Epicor_Supplierconnect_Block_Customer_Orders_Create_Renderer_Confirm', __('Confirm New PO')
            );
            $this->addOption(
                'Epicor_Supplierconnect_Block_Customer_Orders_List_Renderer_State', __('Address State')
            );
        }
        return parent::_toHtml();
    }

}
