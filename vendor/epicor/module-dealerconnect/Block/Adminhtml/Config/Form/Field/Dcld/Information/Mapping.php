<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Dcld\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{
    protected $messageTypes = "DCLD";

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
            $this->addOption('case_number', __('caseNumber'));
            $this->addOption('location_number', __('locationNumber'));
            $this->addOption('serialNumbers > serialNumber', __('serialNumbers > serialNumber'));
            $this->addOption('identification_number', __('identificationNumber'));
            $this->addOption('web_quote_num', __('webQuoteNum'));
            $this->addOption('erp_returns_number', __('erpReturnsNumber'));
            $this->addOption('claim_status', __('claimStatus'));
            $this->addOption('claim_status_change_date', __('claimStatusChangeDate'));
            $this->addOption('claim_update_due_date', __('claimUpdateDueDate'));
        }
        return parent::_toHtml();
    }
}