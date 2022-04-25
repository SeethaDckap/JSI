<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Dcls;


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
        return $this->setExtraParams('rel="<%- renderer %>" style="width:80px"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('Default'));
//            $this->addOption('Epicor_Customerconnect_Block_List_Renderer_Currency', __('Currency'));
//            $this->addOption('Epicor_Customerconnect_Block_List_Renderer_Erpquotestatus', __('Erp Quote Status'));
//            $this->addOption('Epicor_Customerconnect_Block_List_Renderer_ContractCode', __('Contract Code'));
        }
        return parent::_toHtml();
    }

}
