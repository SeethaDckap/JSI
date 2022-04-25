<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Cucs;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{

    protected $messageTypes ="CUCS";

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
            $this->addOption('call_number', __('callNumber'));
            $this->addOption('call_type', __('callType'));
            $this->addOption('requested_date', __('requestedDate'));
            $this->addOption('scheduled_date', __('scheduledDate'));
            $this->addOption('actual_date', __('actualDate'));
            $this->addOption('call_duration', __('callDuration'));
            $this->addOption('service_status', __('serviceStatus'));
            $this->addOption('invoiced', __('invoiced'));
            $this->addOption('call_void', __('callVoid'));
        }
        return parent::_toHtml();
    }

}
