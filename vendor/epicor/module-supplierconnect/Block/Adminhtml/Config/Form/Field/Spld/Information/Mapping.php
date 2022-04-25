<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Spld\Information;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\InformationMapping
{
    protected $messageTypes = "SPLD";

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
            $this->addOption('effective_date', __('effectiveDate'));
            $this->addOption('expires_Date', __('expiresDate'));
            $this->addOption('lead_days', __('leadDays'));
            $this->addOption('quantity_on_hand', __('quantityOnHand'));
            $this->addOption('reference', __('reference'));
            $this->addOption('price_comments', __('priceComments'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('minimum_price', __('minimumPrice'));
            $this->addOption('base_unit_price', __('baseUnitPrice'));
            $this->addOption('price_per', __('pricePer'));
            $this->addOption('discount_percent', __('discountPercent'));
            $this->addOption('price_break_modifier', __('priceBreakModifier'));
        }
        return parent::_toHtml();
    }

}
