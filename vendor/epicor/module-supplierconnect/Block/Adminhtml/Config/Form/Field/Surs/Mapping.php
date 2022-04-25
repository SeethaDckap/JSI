<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Surs;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{

    protected $messageTypes ="SURS";

    protected $gridMappingHelper;

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
        return $this->setName($value);
    }

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- index %>" style="width:200px"');
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('rfq_number', __('rfqNumber'));
            $this->addOption('line', __('line'));
            $this->addOption('due_date', __('dueDate'));
            $this->addOption('respond_date', __('respondDate'));
            $this->addOption('decision_date', __('decisionDate'));
            $this->addOption('product_code', __('productCode'));
            $this->addOption('cross_reference', __('crossReference'));
            $this->addOption('description', __('description'));
            $this->addOption('status', __('status'));
            $this->addOption('response', __('response'));
        }
        return parent::_toHtml();
    }

}