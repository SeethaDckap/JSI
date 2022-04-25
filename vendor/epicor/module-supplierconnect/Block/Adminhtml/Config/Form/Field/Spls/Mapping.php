<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Adminhtml\Config\Form\Field\Spls;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="SPLS";

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
            $this->addOption('cross_reference', __('crossReference'));
            $this->addOption('cross_reference_type', __('crossReferenceType'));
            $this->addOption('operational_code', __('operationalCode'));
            $this->addOption('effective_date', __('effectiveDate'));
            $this->addOption('expiration_date', __('expirationDate'));
            $this->addOption('unit_of_measure_code', __('unitOfMeasureCode'));
            $this->addOption('currency_code', __('currencyCode'));
            $this->addOption('price', __('price'));
        }
        return parent::_toHtml();
    }

}
