<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Spcs;


class Options extends \Magento\Framework\View\Element\Html\Select
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
        return $this->setExtraParams('rel="<%- options %>" style="width:80px"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('None'));
            $this->addOption('supplierconnect/config_source_orderstatusoptions', __('Open'));
        }
        return parent::_toHtml();
    }

}
