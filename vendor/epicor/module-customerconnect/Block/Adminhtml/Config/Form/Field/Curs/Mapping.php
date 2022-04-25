<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Curs;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{

    protected $messageTypes ="CURS";

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

            $this->addOption('returns_number', __('returnsNumber'));
            $this->addOption('line', __('line'));
            $this->addOption('rma_date', __('rmaDate'));
            $this->addOption('product_code', __('productCode'));
            $this->addOption('revision_level', __('revisionLevel'));
            $this->addOption('quantities_ordered', __('quantities > ordered'));
            $this->addOption('quantities_returned', __('quantities > returned'));
            $this->addOption('returns_status', __('returnsStatus'));
            $this->addOption('order_number', __('orderNumber'));
            $this->addOption('order_line', __('orderLine'));
        }
        return parent::_toHtml();
    }

}
