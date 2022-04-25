<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Config\Form\Field\Debm;


class InformationLayout extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_messageBase = 'dealerconnect';
    protected $_messageType = 'debm';
    protected $_allowOptions = true;
    protected $_mappingRenderer;
    protected $_typeRenderer;
    protected $_hiddenRenderer;


    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;

    protected $_messageSection = 'information_section';

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
        if (!$this->_mappingRenderer) {
            $this->_mappingRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Information\\Mapping", '', ['data' => ['is_render_to_js_template' => true]]
            );

            //  $renderer->addOption('pac_Color', __('pac_Color > pac_Color'));
            $this->_mappingRenderer->setInputName('index')->setClass('rel-to-selected debminfomappingrenderer');
        }
        return $this->_mappingRenderer;
    }


    protected function _getTypeRenderer()
    {
        if (!$this->_typeRenderer) {
            $this->_typeRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Information\\Type", '',['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeRenderer->setInputName('type')->setClass('rel-to-selected debminfotype');
        }
        return $this->_typeRenderer;
    }


    protected function _getHiddenRenderer()
    {
        if (!$this->_hiddenRenderer) {
            $this->_hiddenRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Information\\Hidden", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_hiddenRenderer->setInputName('hiddenpac')->setClass('rel-to-selected debmhidden');
        }
        return $this->_hiddenRenderer;
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


    protected function _construct()
    {

        $this->setHtmlId('_debminformation');
        parent::_construct();
    }

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->layoutInterface = $context->getLayout();
        $this->setHtmlId('_debminformation');

        $this->addColumn('header', array(
            'label' => __('Header'),
            'style' => 'width:190px'
        ));

        $this->addColumn('index', array(
            'label' => __('Mapping'),
            'style' => 'width: 50%;',
            'renderer' => $this->_getMappingRenderer(),
        ));

        $this->addColumn('hiddenpac', array(
            'label' => __('hiddenpac'),
            'renderer' => $this->_getHiddenRenderer(),
        ));



        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::__construct(
            $context,
            $data
        );

    }



    public function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= '
            <style>
                .rel-to-selected.debmtype {width: 100% !important;}
                .rel-to-selected.debmfilter_by {width: 100% !important;}
                #grid_debminformation table th:nth-child(3){ display:none;} 
                #grid_debminformation table td:nth-child(3){ display:none;}      
                #grid_debminformation table.admin__control-table .altdebm td {  }
                #grid_debminformation table.admin__control-table td { background-color:transparent ;  }
                #grid_debminformation table.admin__control-table { background-color: #f2f2f2; }
                #grid_debminformation table.admin__control-table th { background-color:#5f564f; color:#fff; }
                #grid_debminformation select {
                    font-size: 13px;
                }   
                #grid_debminformation tbody tr:hover {
                  background-color: #e5f7fe;
                }
                #grid_debminformation td {
                    padding-left: 2px !important;
                    padding-right: 5px !important;
                }   
                
                #grid_debminformation {
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
        $html .='<table cellpadding="2" cellspacing="4" style="border: 1px solid burlywood;border-top:0px;margin-top: -6px;border-collapse: separate;border-radius: 5px;background: oldlace;"><tr><td>'
            . '<h1 style="padding:0px 0px 0px 10px">Information Section</h1>'
            . '<table style="margin-left: 10px;">'
            . '<tr><td>'
            . '<select id="debm_address_column" name="debm_address_column" class=" select admin__control-select" data-ui-id="">'
            . '<option value="1" selected="selected">Column</option></select>'
            . '</td>'
            . '<td>'
            . '<select id="debm_address_column_count" name="debm_address_column_count" class=" select admin__control-select" data-ui-id="">'
            . '<option value="2" selected="selected">3</option></select>'
            . '</td>'
            . '<td>'
            . '<select id="debm_address_column_section" name="debm_address_column_section" class=" select admin__control-select" data-ui-id="">'
            . '<option value="2" selected="selected">Information</option></select>'
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