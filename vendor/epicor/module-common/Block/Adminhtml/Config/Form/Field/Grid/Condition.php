<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid;


class Condition extends \Magento\Framework\View\Element\Html\Select
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
        //return $this->setExtraParams('rel="#{condition}" style="width:80px"');
        return $this->setExtraParams('rel="<%- condition %>" style="width:80px"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('EQ', __('EQ'));
            $this->addOption('NEQ', __('NEQ'));
            $this->addOption('LIKE', __('LIKE'));
            $this->addOption('LT', __('LT'));
            $this->addOption('GT', __('GT'));
            $this->addOption('LTE', __('LTE'));
            $this->addOption('GTE', __('GTE'));
            $this->addOption('LT/GT', __('LT > GT'));
            $this->addOption('LTE/GTE', __('LTE > GTE'));
        }
        return parent::_toHtml();
    }

}
