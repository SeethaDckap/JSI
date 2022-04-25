<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Surd\LineInformation;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{
    protected $messageTypes = "SURD";

    protected $messageSection = "lineinformation_section";

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
            $this->addOption('product_code', __('productCode'));
            $this->addOption('description', __('description'));
            $this->addOption('unit_of_measure_description', __('unitOfMeasureDescription'));
            $this->addOption('quantities', __('quantities'));
            $this->addOption('response', __('response'));
            $this->addOption('line_comments', __('lineComments'));
        }
        return parent::_toHtml();
    }

}
