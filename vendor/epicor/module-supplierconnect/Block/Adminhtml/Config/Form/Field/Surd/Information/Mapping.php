<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Surd\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{
    protected $messageTypes = "SURD";

    protected $gridMappingHelper;

    protected $_gridMessageSection;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Epicor\Common\Helper\GridMapping $gridMappingHelper,
        array $data = []
    )
    {
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

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- index %>" style="width:50%"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('rfq_number', __('rfqNumber'));
            $this->addOption('line', __('line'));
            $this->addOption('rfq_date', __('rfqDate'));
            $this->addOption('respond_date', __('respondDate'));
            $this->addOption('decision_date', __('decisionDate'));
            $this->addOption('header_comment', __('headerComment'));
        }
        return parent::_toHtml();
    }

}
