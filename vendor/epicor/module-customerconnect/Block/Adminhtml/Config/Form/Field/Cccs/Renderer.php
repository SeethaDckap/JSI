<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Cccs;


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
            $this->addOption('Epicor_Customerconnect_Block_Customer_List_Renderer_DeliveryAddress', __('Delivery Address'));
        }
        return parent::_toHtml();
    }

}
