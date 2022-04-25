<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Debm\Additional;

class Grid extends \Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid {

    protected $_messageBase = 'dealerconnect';
    protected $_messageType = 'debm';
    protected $_allowOptions = true;
    protected $_showDropFilter = true;
    protected $_showVisibleFilter = true;
    protected $_messageSection = 'replacement_grid_config';


    protected function _getMappingRenderer()
    {
        if (!$this->_mappingRenderer) {
            $this->_mappingRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Mapping", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mappingRenderer->setInputName('index')->setClass('rel-to-selected mappingrenderer');
        }
        return $this->_mappingRenderer;
    }

    protected function _getFilterByRenderer()
    {
        if (!$this->_filterByRenderer) {
            $this->_filterByRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Filterby', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_filterByRenderer->setInputName('filter_by')->setClass('rel-to-selected debmfilter_by');
        }
        return $this->_filterByRenderer;
    }

    protected function _getVisibleRenderer()
    {
        if (!$this->_visibleRenderer) {
            $this->_visibleRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Visible", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_visibleRenderer->setInputName('visible')->setClass('rel-to-selected debmvisible');
        }
        return $this->_visibleRenderer;
    }

    protected function _getFilterRenderer()
    {
        if (!$this->_filterRenderer) {
            $this->_filterRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Showfilter", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_filterRenderer->setInputName('showfilter')->setClass('rel-to-selected debmshowfilter');
        }
        return $this->_filterRenderer;
    }

    protected function _getPacRenderer()
    {
        if (!$this->_pacRenderer) {
            $this->_pacRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Hidden", '', ['data' => ['is_render_to_js_template' => false]]
            );
            $this->_pacRenderer->setInputName('pacattributes')->setClass('rel-to-selected pac_attribute_value');
        }
        return $this->_pacRenderer;
    }

    protected function _getDatatypeRenderer()
    {
        if (!$this->_datatypeRenderer) {
            $this->_datatypeRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Datatype", '', ['data' => ['is_render_to_js_template' => false]]
            );
            $this->_datatypeRenderer->setInputName('datatypejson')->setClass('rel-to-selected pac_attribute_value');
        }
        return $this->_datatypeRenderer;
    }

    protected function _getConditionRenderer()
    {

        if (!$this->_conditionRenderer) {
            $this->_conditionRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Condition', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_conditionRenderer->setInputName('condition')->setClass('rel-to-selected debmcondition');
        }
        return $this->_conditionRenderer;
    }

    protected function _getSortTypeRenderer()
    {
        if (!$this->_sortTypeRenderer) {
            $this->_sortTypeRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Sorttype', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_sortTypeRenderer->setInputName('sort_type')->setClass('rel-to-selected debmsortby');
        }
        return $this->_sortTypeRenderer;
    }

    protected function _getTypeRenderer()
    {
        if (!$this->_typeRenderer) {
            $this->_typeRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Type', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeRenderer->setInputName('type')->setClass('rel-to-selected debmtype');
        }
        return $this->_typeRenderer;
    }

    protected function _getOptionsRenderer()
    {
        if (!$this->_optionsRenderer) {
            $this->_optionsRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Options", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_optionsRenderer->setInputName('options')->setClass('rel-to-selected');
        }
        return $this->_optionsRenderer;
    }

    protected function _getRendererRenderer()
    {
        if (!$this->_rendererRenderer) {
            $this->_rendererRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Renderer", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_rendererRenderer->setInputName('renderer')->setClass('rel-to-selected');
        }
        return $this->_rendererRenderer;
    }

    /**
     * Initialize object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setHtmlId('_add_debm');

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
        $this->setHtmlId('_add_debm');
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= '
            <style>
                .rel-to-selected.debmtype {width: 100% !important;}
                .rel-to-selected.debmfilter_by {width: 100% !important;}
                #grid_add_debm table th:nth-child(11){ display:none;} 
                 #grid_add_debm table th:nth-child(12){ display:none;} 
                //#grid_add_debm table td:nth-child(12){ display:none;} 
                #grid_add_debm table td:nth-child(11){ display:none;}      
                #grid_add_debm table td:nth-child(12){ display:none;}      
                #grid_add_debm table th:nth-child(8){ display:none;} 
                #grid_add_debm table td:nth-child(8){ display:none;}      
               // #grid_add_debm {width:700px;overflow-x:scroll;overflow-y:scroll;} 
                .altdebm {} 
                #grid_add_debm table.admin__control-table .altdebm td {  }
                #grid_add_debm table.admin__control-table td { background-color:transparent ;  }
                #grid_add_debm table.admin__control-table { background-color: #f2f2f2; }
                #grid_add_debm table.admin__control-table th { background-color:#5f564f; color:#fff; }
                #grid_add_debm select {
                    font-size: 13px;
                }   
                #grid_add_debm tbody tr:hover {
                  background-color: #e5f7fe;
                }
                #grid_add_debm td {
                    padding-left: 2px !important;
                    padding-right: 5px !important;
                }   
                
                #grid_add_debm {
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
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p></br>';
        }
        $html .= '</td>';
        return $html;
    }

}