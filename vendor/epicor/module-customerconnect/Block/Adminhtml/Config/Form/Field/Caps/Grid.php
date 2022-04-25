<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Config\Form\Field\Caps;


class Grid extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid
{

    protected $_messageBase = 'customerconnect';
    protected $_messageType = 'caps';
    protected $_allowOptions = true;
    
    protected function _construct()
    {
        parent::_construct();
        $this->setHtmlId('_caps');
        
    }
    
    
    public function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= '
            <style>
                .rel-to-selected.deistype {width: 100% !important;}
                .rel-to-selected.deisfilter_by {width: 100% !important;}
                #grid_caps table.admin__control-table .altdeis td {  }
                #grid_caps table.admin__control-table td { background-color:transparent ;  }
                #grid_caps table.admin__control-table { background-color: #f2f2f2; }
                #grid_caps table.admin__control-table th { background-color:#5f564f; color:#fff; }
                #grid_caps select {
                    font-size: 13px;
                }   
                #grid_caps tbody tr:hover {
                  background-color: #e5f7fe;
                }
                #grid_caps td {
                    padding-left: 2px !important;
                    padding-right: 5px !important;
                }   
                
                #grid_caps {
                    border: 0.1rem solid #8a837f;
                }
            </style>            
                ';
        return $html;
    }     
    
    
    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $html = '';
        $html .= $this->_renderValue($element);

        return $this->_decorateRowHtml($element, $html);
    }      
    
    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="" colspan="5">';
        $html .= '<label for="' .
            $element->getHtmlId() . '"><span style="font-weight: bold;"' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label>';
        $html .= $this->_getElementHtml($element);
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';
        return $html;
    }        

}
