<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid;


class Filterby extends \Magento\Framework\View\Element\Html\Select
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
        //return $this->setExtraParams('rel="#{filter_by}" style="width:50px"');
        return $this->setExtraParams('rel="<%- filter_by %>" style="width:50px"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('erp', __('ERP'));
            $this->addOption('linq', __('ECC'));
            $this->addOption('none', __('No Filter'));
        }
        return parent::_toHtml();
    }

}
