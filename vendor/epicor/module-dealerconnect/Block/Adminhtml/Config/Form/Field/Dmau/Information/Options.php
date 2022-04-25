<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Dmau\Information;


class Options extends \Magento\Framework\View\Element\Html\Select
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
        //return $this->setExtraParams('rel="#{options}" style="width:80px"');
        return $this->setExtraParams('rel="<%- options %>" style="width:92px"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('None'));
           // $this->addOption('customerconnect/erp_mapping_erporderstatus', __('Erp Order Status'));
        }
        return parent::_toHtml();
    }

}
