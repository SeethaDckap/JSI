<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Debm;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="DEBM";

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
        if (strpos($value, 'grid_config_additional') !== false) {
            $this->_gridMessageSection = "replacement_grid_config";
        }
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function setColumnName($value)
    {

        return $this->setExtraParams('rel="<%- index %>" style="width:151px"');

    }


    public function _toHtml()
    {

        if (!$this->_beforeToHtml()) {
            return '';
        }

        if (!$this->getOptions()) {
            $this->addOption('expand', __('expand'));
            $this->addOption('product_code', __('productCode'));
            $this->addOption('description', __('description'));
            $this->addOption('unit_of_measure_code', __('unitOfMeasureCode'));
            $this->addOption('quantity', __('quantity'));
            $this->addOption('serial_numbers_serial_number', __('serialNumbers > serialNumber'));
            $this->addOption('original_product_code', __('original > productCode'));
            $this->addOption('new_product_code', __('new > productCode'));
            $this->addOption('new_serial_number', __('new > serialNumber'));
            $this->addOption('reorder', __('reorder'));
        }
        return parent::_toHtml();
    }

}
