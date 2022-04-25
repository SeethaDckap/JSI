<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Deis;


class Grid extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid
{

    protected $_messageBase = 'dealerconnect';
    protected $_messageType = 'deis';
    protected $_allowOptions = true;
    protected $_showDropFilter = true;
    protected $_showVisibleFilter = true;    
    
    
    protected function _getMappingRenderer()
    {
        if(!$this->_mappingRenderer) {
            $this->_mappingRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Mapping", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mappingRenderer->setInputName('index')->setClass('rel-to-selected mappingrenderer');
        }
        return $this->_mappingRenderer;
    }    
    
    
    protected function _getFilterByRenderer()
    {
        if(!$this->_filterByRenderer) {
            $this->_filterByRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Filterby', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_filterByRenderer->setInputName('filter_by')->setClass('rel-to-selected deisfilter_by');
        }
        return $this->_filterByRenderer;
    }    
    
    protected function _getVisibleRenderer()
    {
        if(!$this->_visibleRenderer) {
            $this->_visibleRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Visible", '', ['data' => ['is_render_to_js_template' => true]]
            );        

            $this->_visibleRenderer->setInputName('visible')->setClass('rel-to-selected deisvisible');
        }
        return $this->_visibleRenderer;
    }  
    
    
    protected function _getFilterRenderer()
    {
        if(!$this->_filterRenderer) {
            $this->_filterRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Showfilter", '', ['data' => ['is_render_to_js_template' => true]]
            );      
            $this->_filterRenderer->setInputName('showfilter')->setClass('rel-to-selected deisshowfilter');
        }
        return $this->_filterRenderer;
    }      
    
    
    protected function _getPacRenderer()
    {
        if(!$this->_pacRenderer) {
            $this->_pacRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Hidden", '', ['data' => ['is_render_to_js_template' => true]]
            );      
            $this->_pacRenderer->setInputName('pacattributes')->setClass('rel-to-selected pac_attribute_value');
        }
        return $this->_pacRenderer;
    }   
    
    protected function _getDatatypeRenderer()
    {
        if(!$this->_datatypeRenderer) {
            $this->_datatypeRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Datatype", '', ['data' => ['is_render_to_js_template' => true]]
            );      
            $this->_datatypeRenderer->setInputName('datatypejson')->setClass('rel-to-selected pac_datatype_value');
        }
        return $this->_datatypeRenderer;
    }     
    
    
    protected function _getConditionRenderer()
    {
        if(!$this->_conditionRenderer) {
            $this->_conditionRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Condition', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_conditionRenderer->setInputName('condition')->setClass('rel-to-selected deiscondition');
        }
        return $this->_conditionRenderer;
    }
    
    protected function _getSortTypeRenderer()
    {
        if(!$this->_sortTypeRenderer) {
            $this->_sortTypeRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Sorttype', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_sortTypeRenderer->setInputName('sort_type')->setClass('rel-to-selected deissortby');
        }
        return $this->_sortTypeRenderer;
    }    
    
    
    protected function _getTypeRenderer()
    {
        if(!$this->_typeRenderer) {
            $this->_typeRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Type', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeRenderer->setInputName('type')->setClass('rel-to-selected deistype');
        }
        return $this->_typeRenderer;
    }    
 
    

    /**
     * Initialize object
     *
     * @return void
     */
    protected function _construct()
    {
        
        $this->setHtmlId('_deis');
        
        $this->addColumn('type', array(
            'label' => __('Type'),
            'style' => 'width:108px',
            'renderer' => $this->_getTypeRenderer()
        ));        
        
        $this->addColumn('index', array(
            'label' => __('Mapping'),
            'style' => 'width:275px',
            'renderer' => $this->_getMappingRenderer(),
        ));      
        
        $this->addColumn('filter_by', array(
            'label' => __('Filter By'),
            'style' => 'width:75px',
            'renderer' => $this->_getFilterByRenderer()
        ));       
        
        $this->addColumn('condition', array(
            'label' => __('Condition'),
            'style' => 'width:80px',
            'renderer' => $this->_getConditionRenderer()
        ));        
        
        $this->addColumn('sort_type', array(
            'label' => __('Sort Type'),
            'style' => 'width:75px',
            'renderer' => $this->_getSortTypeRenderer()
        ));        
        
        $this->addColumn('visible', array(
            'label' => __('Visible'),
            'style' => 'width:75px',
            'renderer' => $this->_getVisibleRenderer()
        ));

        $this->addColumn('showfilter', array(
            'label' => __('Filter'),
            'style' => 'width:75px',
            'renderer' => $this->_getFilterRenderer()
        ));       

        $this->addColumn('pacattributes', array(
            'label' => __('Pac Atrributes'),
            //'style' => 'display:none;',
            'renderer' => $this->_getPacRenderer()
        )); 
        
        $this->addColumn('datatypejson', array(
            'label' => __('DataType'),
            //'style' => 'display:none;',
            'renderer' => $this->_getDatatypeRenderer()
        ));         
        
        parent::_construct();
    }    
    
    
    public function checkRendererPac($element) {
        $pac = array();
        foreach($element->getValue() as $pacCheck) {
           $pacVals = explode('_',$pacCheck['index'],2);
           if(strpos($pacCheck['index'], "pac_") === 0) {
               $pac['value'] = $pacCheck['index'];
               $pac['label'] = $pacVals[1];
           } else {
               $pac = array();
           }
        }
        return $pac;
    }
    
    
    public function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= '
            <style>
                .rel-to-selected.deistype {width: 100% !important;}
                .rel-to-selected.deisfilter_by {width: 100% !important;}
                #grid_deis table th:nth-child(11){ display:none;} 
                 #grid_deis table th:nth-child(12){ display:none;} 
                //#grid_deis table td:nth-child(12){ display:none;} 
                #grid_deis table td:nth-child(11){ display:none;}      
                #grid_deis table td:nth-child(12){ display:none;}      
                #grid_deis table th:nth-child(8){ display:none;} 
                #grid_deis table td:nth-child(8){ display:none;}      
               // #grid_deis {width:700px;overflow-x:scroll;overflow-y:scroll;} 
                .altdeis {} 
                #grid_deis table.admin__control-table .altdeis td {  }
                #grid_deis table.admin__control-table td { background-color:transparent ;  }
                #grid_deis table.admin__control-table { background-color: #f2f2f2; }
                #grid_deis table.admin__control-table th { background-color:#5f564f; color:#fff; }
                #grid_deis select {
                    font-size: 13px;
                }   
                #grid_deis tbody tr:hover {
                  background-color: #e5f7fe;
                }
                #grid_deis td {
                    padding-left: 2px !important;
                    padding-right: 5px !important;
                }   
                
                #grid_deis {
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
