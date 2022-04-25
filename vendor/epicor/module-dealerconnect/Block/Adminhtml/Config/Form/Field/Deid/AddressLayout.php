<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Deid;


class AddressLayout extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_messageBase = 'dealerconnect';
    protected $_messageType = 'deid';
    protected $_allowOptions = true;
    protected $_deidMappingRenderer;


    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;

    protected $_pacRenderer;

    protected $_datatypeRenderer;

    protected $_conditionRenderer;

    protected $_typeRenderer;

    protected $_messageSection = 'address_section';

    protected $_template = 'Epicor_Common::widget/grid/array.phtml';



    public function getMessageTypes()
    {
        return $this->_messageType;
    }

    public function getMessageSection()
    {
        return $this->_messageSection;
    }

    protected function _getMappingRenderer()
    {
        if(!$this->_deidMappingRenderer) {
            $this->_deidMappingRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Address\\Mapping", '', ['data' => ['is_render_to_js_template' => true]]
            );

            //  $renderer->addOption('pac_Color', __('pac_Color > pac_Color'));
            $this->_deidMappingRenderer->setInputName('index')->setClass('rel-to-selected mappingrenderer');
        }
        return $this->_deidMappingRenderer;
    }

    protected function _getPacRenderer()
    {
        if(!$this->_pacRenderer) {
            $this->_pacRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Address\\Hidden", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_pacRenderer->setInputName('pacattributes')->setClass('rel-to-selected pac_attribute_value');
        }
        return $this->_pacRenderer;
    }

    protected function _getDatatypeRenderer()
    {
        if(!$this->_datatypeRenderer) {
            $this->_datatypeRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Address\\Datatype", '', ['data' => ['is_render_to_js_template' => true]]
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

    protected function _getTypeRenderer()
    {
        if(!$this->_typeRenderer) {
            $this->_typeRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Address\\Type", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeRenderer->setInputName('type')->setClass('rel-to-selected deistype');
        }
        return $this->_typeRenderer;
    }


    protected function _construct()
    {

        $this->setHtmlId('_deid');
        parent::_construct();
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMappingRenderer()->calcOptionHash($row->getData('index'))] = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->layoutInterface = $context->getLayout();
        $this->setHtmlId('_deid');

        $this->addColumn('header', array(
            'label' => __('Header'),
            'style' => 'width:120px'
        ));
//        
//        $this->addColumn('type', array(
//            'label' => __('Type'),
//            'style' => 'width:108px',
//            'renderer' => $this->_getTypeRenderer()
//        ));        

        $this->addColumn('index', array(
            'label' => __('Mapping'),
            'style' => 'width:275px',
            'renderer' => $this->_getMappingRenderer(),
        ));

//        $this->addColumn('datatypejson', array(
//            'label' => __('DataType'),
//            //'style' => 'display:none;',
//            'renderer' => $this->_getDatatypeRenderer()
//        ));       
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::__construct(
            $context,
            $data
        );

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
                #grid_deid table th:nth-child(11){ display:none;} 
                 #grid_deid table th:nth-child(12){ display:none;} 
                //#grid_deid table td:nth-child(12){ display:none;} 
                #grid_deid table td:nth-child(11){ display:none;}      
                #grid_deid table td:nth-child(12){ display:none;}      
                #grid_deid table th:nth-child(8){ display:none;} 
                #grid_deid table td:nth-child(8){ display:none;}      
               // #grid_deid {width:700px;overflow-x:scroll;overflow-y:scroll;} 
                .altdeis {} 
                #grid_deid table.admin__control-table .altdeis td {  }
                #grid_deid table.admin__control-table td { background-color:transparent ;  }
                #grid_deid table.admin__control-table { background-color: #f2f2f2; }
                #grid_deid table.admin__control-table th { background-color:#5f564f; color:#fff; }
                #grid_deid select {
                    font-size: 13px;
                }   
                #grid_deid tbody tr:hover {
                  background-color: #e5f7fe;
                }
                #grid_deid td {
                    padding-left: 2px !important;
                    padding-right: 5px !important;
                }   
                
                #grid_deid {
                    border: 0.1rem solid #8a837f;
                    margin-bottom: 10px;
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

        $html .='<table cellpadding="2" cellspacing="4" style="border: 1px solid burlywood;margin-top:24px;border-collapse: separate;border-radius: 5px;background: oldlace;"><tr><td>'
            . '<h1 style="padding:0px 0px 0px 10px">Address Section</h1>'
            . '<table style="margin-left: 10px;">'
            . '<tr><td>'
            . '<select id="deid_address_column" name="deid_address_column" class=" select admin__control-select" data-ui-id="">'
            . '<option value="1" selected="selected">Column</option></select>'
            . '</td>'
            . '<td>'
            . '<select id="deid_address_column_count" name="deid_address_column_count" class=" select admin__control-select" data-ui-id="">'
            . '<option value="2" selected="selected">2</option></select>'
            . '</td>'
            . '<td>'
            . '<select id="deid_address_column_section" name="deid_address_column_section" class=" select admin__control-select" data-ui-id="">'
            . '<option value="2" selected="selected">Address</option></select>'
            . '</td>'
            . '</tr>'
            . '</table></td';
        $html .= '<br>';
        $html .='<div style="margin-left:10px;margin-top: 12px;">';
        $html .= $this->_getElementHtml($element);
        if ($element->getComment()) {
            //  $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .='</div>';
        $html .= '</td></tr></table>';
        return $html;
    }


    public function getArrayRows()
    {
        $result = [];
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();
        if (!is_array($element->getValue())) {
            $value = $element->getValue();
            $element->setValue(empty($value) ? false : unserialize($value));
        }

        if ($element->getValue() && is_array($element->getValue())) {
            foreach ($element->getValue() as $rowId => $row) {
                $rowColumnValues = [];
                foreach ($row as $key => $value) {
                    $row[$key] = $value;
                    $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key];
                }
                $row['_id'] = $rowId;
                $row['column_values'] = $rowColumnValues;
                $result[$rowId] = new \Magento\Framework\DataObject($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        return $result;
    }



}