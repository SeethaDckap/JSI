<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Deid\Grid;


class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Mapping
{
    protected $messageTypes ="DEID";

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

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- index %>" style="width:250px"');
    }


    public function _toHtml()
    {

        if (!$this->_beforeToHtml()) {
            return '';
        }

        if (!$this->getOptions()) {
            $this->addOption('address', __('Address'));
            $this->addOption('comment', __('Transaction Comment'));
            $this->addOption('created_on_date', __('created On Date'));
            $this->addOption('effective_date', __('effective Date'));
            $this->addOption('warranty_code', __('warranty Code'));
            $this->addOption('warranty_comment', __('warrantyComment'));
            $this->addOption('warranty_expiration_date', __('warranty Expiration Date'));
            $this->addOption('warranty_start_date', __('warranty Start Date'));
        }
        return parent::_toHtml();
    }

}
