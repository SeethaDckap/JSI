<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\SalesRep\Block\Widget\Grid\Column\Renderer;



class Date extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
     /**
     * @var \Epicor\Common\Helper\Locale\Format\Date
     */
    protected $commonLocaleFormatDateHelper;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper,  
        array $data = []
    ) {
        $this->commonLocaleFormatDateHelper = $commonLocaleFormatDateHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    
     /**
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    { 
        $val = $row->getData($this->getColumn()->getIndex());
        return $this->commonLocaleFormatDateHelper->getLocalFormatDate($val, \IntlDateFormatter::MEDIUM);
    }
    
}