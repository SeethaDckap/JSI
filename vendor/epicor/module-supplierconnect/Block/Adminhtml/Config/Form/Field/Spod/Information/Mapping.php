<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Spod\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{
    protected $messageTypes = "SPOD";

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
        //M1 > M2 Translation Begin (Rule 22)
        //return $this->setExtraParams('rel="#{index}" style="width:200px"');
        return $this->setExtraParams('rel="<%- index %>" style="width:50%"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('order_date', __('orderDate'));
            $this->addOption('due_date', __('dueDate'));
            $this->addOption('promise_date', __('promiseDate'));
            $this->addOption('post_date', __('postDate'));
            $this->addOption('order_status', __('orderStatus'));
            $this->addOption('payment_terms', __('paymentTerms'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('fob', __('fob'));
            $this->addOption('order_confirmed', __('orderConfirmed'));
        }
        return parent::_toHtml();
    }

}
