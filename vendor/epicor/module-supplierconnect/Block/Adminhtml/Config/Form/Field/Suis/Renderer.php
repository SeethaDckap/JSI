<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Suis;


class Renderer extends \Magento\Framework\View\Element\Html\Select
{
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
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
        //M1 > M2 Translation Begin (Rule 22)
        //return $this->setExtraParams('rel="#{renderer}" style="width:80px"');
        return $this->setExtraParams('rel="<%- renderer %>" style="width:80px"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('Default'));
            $this->addOption('Epicor_Supplierconnect_Block_List_Renderer_Currency', __('Currency'));
            $this->addOption('Epicor_Supplierconnect_Block_Customer_Invoices_List_Renderer_Invoicestatus', __('Invoice status'));
        }
        return parent::_toHtml();
    }

}
