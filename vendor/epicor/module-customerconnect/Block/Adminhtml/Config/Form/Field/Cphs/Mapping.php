<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Cphs;

class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{

    protected $messageTypes ="CPHS";

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
            $this->addOption('product_code', __('productCode'));
            $this->addOption('unit_of_measure_code', __('unitOfMeasureCode'));
            $this->addOption('total_qty_ordered', __('totalQtyOrdered'));
            $this->addOption('last_ordered_date', __('lastOrderedDate'));
            $this->addOption('last_order_number', __('lastOrderNumber'));
            $this->addOption('last_tracking_number', __('lastTrackingNumber'));
            $this->addOption('last_ordered_status', __('lastOrderedStatus'));
            $this->addOption('last_packing_slip', __('lastPackingSlip'));
        }
        return parent::_toHtml();
    }

}
