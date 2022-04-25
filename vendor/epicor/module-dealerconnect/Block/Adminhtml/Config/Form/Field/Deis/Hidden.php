<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Deis;


class Hidden extends \Magento\Framework\View\Element\Html\Date
{
    
    
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setColumnName($value)
    {
        //M1 > M2 Translation Begin (Rule 22)
        //return $this->setExtraParams('rel="#{options}" style="width:80px"');
        return $this->setExtraParams('rel="<%- pacattributes %>"  value="<%- pacattributes %>"');
        //M1 > M2 Translation End
    }
    
    public function setInputId($value)
    {
        return $this->setId($value);
    }    
    
    /**
     * Render block HTML
     *
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _toHtml()
    {
        if (!$this->_beforeToHtml()) {
            return '';
        }        
        //$html ='<script>var pacattributes="";</script>';
        $html = '<input type="hidden" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'class="' . $this->getClass() . '" ' . $this->getExtraParams() . '/> ';
        return $html;
    }
    
    
  

}