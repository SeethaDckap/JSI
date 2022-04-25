<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Config\Form\Field;


abstract class InformationLayout extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_messageBase;
    protected $_messageType;
    protected $_allowOptions;
    protected $_mappingRenderer;
    protected $_typeRenderer;
    protected $_hiddenRenderer;

    protected $_messageSection = 'information_section';
    protected $_sectionTitle = 'Information Section';

    protected $_template = 'Epicor_Common::widget/grid/array.phtml';

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;


    abstract public function getMessageTypes();

    abstract public function getMessageSection();


    abstract public function _getMappingRenderer();


    abstract public function _getTypeRenderer();

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMappingRenderer()->calcOptionHash($row->getData('index'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }


    protected function _construct()
    {

        $this->setHtmlId($this->_messageType.'_'.$this->_messageSection.'_information');
        parent::_construct();
    }

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->layoutInterface = $context->getLayout();
        $this->setHtmlId($this->_messageType.'_information');
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::__construct(
            $context,
            $data
        );

    }


    public function _toHtml()
    {
        $htmlId = "#grid" . $this->getHtmlId();
        $combineBoth = $htmlId . " " . "#addRow" . $this->getHtmlId();
        $html = parent::_toHtml();
        $html .= '
            <style>
                .rel-to-selected.type {width: 100% !important;}
                .rel-to-selected.filter_by {width: 100% !important;}  
                #grid_information table.admin__control-table .alt td {  }
                #grid_information table.admin__control-table td { background-color:transparent ;  }
                #grid_information table.admin__control-table { background-color: #f2f2f2; }
                #grid_information table.admin__control-table th { background-color:#5f564f; color:#fff; }
                #grid_information select {
                    font-size: 13px;
                }   
                #grid_information tbody tr:hover {
                  background-color: #e5f7fe;
                }
                #grid_information td {
                    padding-left: 2px !important;
                    padding-right: 5px !important;
                }   
                
                #grid_information {
                    border: 0.1rem solid #8a837f;
                    margin-bottom: 10px;
                }
            </style>       
            <script>
            require(["jquery","domReady"], function(jQuery,domReady){
                jQuery(document).ready(function () {
                    require(["Epicor_Common/epicor/informationlayout/js/drag"], function(drag) {
                        if (jQuery( "' . $htmlId . '" ).length ) {
                                jQuery("' . $combineBoth . '").tableDnD();
                                jQuery("' . $combineBoth . '  tr:even").addClass("informationdrag");
                        }
                    });
                });
                jQuery("#addToEndBtn' . $this->getHtmlId() . '").on("click",function() {
                  require(["Epicor_Common/epicor/informationlayout/js/drag"], function(drag) {
                       jQuery("' . $combineBoth . '").tableDnDUpdate();
                       jQuery("' . $combineBoth . '  tr:even").addClass("informationdrag"); 
                   });
                });
            });
            </script>      
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
        $html .= '<table cellpadding="2" cellspacing="4" style="border: 1px solid burlywood;margin-top: 4px;border-collapse: separate;border-radius: 5px;background: oldlace;"><tr><td>'
            . '<h1 style="padding:0px 0px 0px 10px">'.$this->_sectionTitle.'</h1>';
        $html .= '<br>';
        $html .= '<div style="margin-left:10px;margin-top: 12px;" id="grid_information">';
        $html .= $this->_getElementHtml($element);
        if ($element->getComment()) {
            //  $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</div>';
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