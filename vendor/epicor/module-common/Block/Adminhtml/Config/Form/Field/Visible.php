<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Config\Form\Field;


class Visible extends \Magento\Framework\View\Element\Html\Select
{
    protected $_options = array('1'=>'Yes','0'=>'No');
    
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setColumnName($value)
    {
        return $this->setExtraParams('rel="<%- visible %>" style="width:45px"');
    }
    
    

    public function optionValues()
    {
        if (!$this->getOptions()) {
            $this->addOption('1', __('Yes'));
            $this->addOption('0', __('No'));
        }
        return parent::_toHtml();
    }

}
