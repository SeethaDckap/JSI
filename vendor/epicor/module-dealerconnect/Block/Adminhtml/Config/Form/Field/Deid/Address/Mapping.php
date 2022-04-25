<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Deid\Address;



class Mapping extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\AddressMapping
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
        return $this->setExtraParams('rel="<%- index %>" style="width:151px"');
    }


    public function _toHtml()
    {

        if (!$this->_beforeToHtml()) {
            return '';
        }

        if (!$this->getOptions()) {
            $this->addOption('location_address', __('Location Address'));
            $this->addOption('soldto_address', __('Sold To Address'));
            $this->addOption('owner_address', __('Owner Address'));
        }
        return parent::_toHtml();
    }

}
