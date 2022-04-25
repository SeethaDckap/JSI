<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Caps;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{

    protected $messageTypes ="CAPS";

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
        //M1 > M2 Translation Begin (Rule 22)
        //return $this->setExtraParams('rel="#{index}" style="width:200px"');
        return $this->setExtraParams('rel="<%- index %>" style="width:200px"');
        //M1 > M2 Translation End
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('invoice_number',__('invoiceNumber'));
            $this->addOption('invoice_date', __('invoiceDate'));
            $this->addOption('due_date', __('dueDate'));
            $this->addOption('original_value', __('originalValue'));
            $this->addOption('payment_value', __('paymentValue'));
            $this->addOption('outstanding_value', __('outstandingValue'));
            $this->addOption('delivery_address', __('deliveryAddress'));

        }
        return parent::_toHtml();
    }

}