<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Config\Form\Field;


class Grid extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_messageBase;
    protected $_messageType;
    protected $_allowOptions = false;
    protected $_showDropFilter = false;
    protected $_showVisibleFilter = false;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;

    protected $_mappingRenderer;
    protected $_typeRenderer;
    protected $_filterByRenderer;
    protected $_conditionRenderer;
    protected $_sortTypeRenderer;
    protected $_optionsRenderer;
    protected $_rendererRenderer;
    protected $_contractCodeRenderer;
    protected $_visibleRenderer;
    protected $_filterRenderer;
    protected $_pacRenderer;
    protected $_datatypeRenderer;
    protected $_messageSection = 'grid_config';
    protected $_mappingGrids;


    protected $_template = 'Epicor_Common::widget/grid/array.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setHtmlId('_'.$this->_messageType);

    }

    protected function _getMappingRenderer()
    {
        if (!$this->_mappingRenderer) {
            $this->_mappingRenderer = $this->layoutInterface->createBlock(
                "Epicor\\". ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\Mapping", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_mappingRenderer->setInputName('index')->setClass('rel-to-selected');
        }
        return $this->_mappingRenderer;
    }

    protected function _getTypeRenderer()
    {
        if (!$this->_typeRenderer) {
            $this->_typeRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Type', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeRenderer->setInputName('type')->setClass('rel-to-selected');
        }
        return $this->_typeRenderer;
    }

    protected function _getFilterByRenderer()
    {
        if (!$this->_filterByRenderer) {
            $this->_filterByRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Filterby', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_filterByRenderer->setInputName('filter_by')->setClass('rel-to-selected');
        }
        return $this->_filterByRenderer;
    }

    protected function _getConditionRenderer()
    {
        if (!$this->_conditionRenderer) {
            $this->_conditionRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Condition', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_conditionRenderer->setInputName('condition')->setClass('rel-to-selected');
        }
        return $this->_conditionRenderer;
    }

    protected function _getSortTypeRenderer()
    {
        if (!$this->_sortTypeRenderer) {
            $this->_sortTypeRenderer = $this->layoutInterface->createBlock(
                'Epicor\Common\Block\Adminhtml\Config\Form\Field\Grid\Sorttype', '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_sortTypeRenderer->setInputName('sort_type')->setClass('rel-to-selected');
        }
        return $this->_sortTypeRenderer;
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

    protected function _getContractCodeRenderer()
    {
        if (!$this->_contractCodeRenderer) {
            $this->_contractCodeRenderer = $this->layoutInterface->createBlock(
                "Epicor\\" . ucfirst($this->_messageBase) . "\\Block\\Adminhtml\\Config\\Form\\Field\\" . ucfirst($this->_messageType) . "\\ContractCode", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_contractCodeRenderer->setInputName('contractcode')->setClass('rel-to-selected');
        }
        return $this->_contractCodeRenderer;
    }

    protected function _getVisibleRenderer()
    {
        if(!$this->_visibleRenderer) {
            $this->_visibleRenderer = $this->layoutInterface->createBlock(
                "Epicor\\Common\\Block\\Adminhtml\\Config\\Form\\Field\\Visible", '', ['data' => ['is_render_to_js_template' => true]]
            );

            $this->_visibleRenderer->setInputName('visible')->setClass('rel-to-selected visible');
        }
        return $this->_visibleRenderer;
    }


    protected function _getFilterRenderer()
    {
        if(!$this->_filterRenderer) {
            $this->_filterRenderer = $this->layoutInterface->createBlock(
                "Epicor\\Common\\Block\\Adminhtml\\Config\\Form\\Field\\Showfilter", '', ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_filterRenderer->setInputName('showfilter')->setClass('rel-to-selected showfilter');
        }
        return $this->_filterRenderer;
    }


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->layoutInterface = $context->getLayout();

        $this->addColumn('header', array(
            'label' => __('Header'),
            'style' => 'width:120px'
        ));

        $this->addColumn('type', array(
            'label' => __('Type'),
            'style' => 'width:50px',
            'renderer' => $this->_getTypeRenderer()
        ));

        if ($this->_allowOptions) {
            $this->addColumn('options', array(
                'label' => __('Options'),
                'style' => 'width:50px',
                'renderer' => $this->_getOptionsRenderer()
            ));
        }

        $this->addColumn('index', array(
            'label' => __('Mapping'),
            'style' => 'width:75px',
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

        $this->addColumn('renderer', array(
            'label' => __('Renderer'),
            'style' => 'width:75px',
            'renderer' => $this->_getRendererRenderer()
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

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::__construct(
            $context,
            $data
        );
    }

    public function getMessageTypes()
    {
        return $this->_messageType;
    }

    public function getMessageSection()
    {
        return $this->_messageSection;
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

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getTypeRenderer()->calcOptionHash($row->getData('type'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getMappingRenderer()->calcOptionHash($row->getData('index'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getFilterByRenderer()->calcOptionHash($row->getData('filter_by'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getConditionRenderer()->calcOptionHash($row->getData('condition'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getSortTypeRenderer()->calcOptionHash($row->getData('sort_type'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getRendererRenderer()->calcOptionHash($row->getData('renderer'))] = 'selected="selected"';
        $arrayOptions = array('cups','cuss','spls','sups');
        if(!in_array(strtolower($this->_messageType), $arrayOptions)) {
            $optionExtraAttr['option_' . $this->_getOptionsRenderer()->calcOptionHash($row->getData('options'))] = 'selected="selected"';
        }
        //if($this->_showVisibleFilter) {
        $optionExtraAttr['option_' . $this->_getVisibleRenderer()->calcOptionHash($row->getData('visible'))] = 'selected="selected"';
        //}
        //if($this->_showDropFilter) {
        $optionExtraAttr['option_' . $this->_getFilterRenderer()->calcOptionHash($row->getData('showfilter'))] = 'selected="selected"';
        //}

        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        $htmlId = "#grid".$this->getHtmlId();
        $combineBoth =$htmlId. " "."#addRow".$this->getHtmlId();
        $html .= '
            <style>
                .rel-to-selected.deistype {width: 100% !important;}
                .rel-to-selected.deisfilter_by {width: 100% !important;}
                '.$htmlId.' .admin__control-table-wrapper { width:100% !important }
                '.$htmlId.' table.admin__control-table .altdeis td {  }
                '.$htmlId.' table.admin__control-table td { background-color:transparent ;  }
                '.$htmlId.' table.admin__control-table { background-color: #f2f2f2; }
                '.$htmlId.' table.admin__control-table th { background-color:#5f564f; color:#fff; }
                '.$htmlId.' select {
                    font-size: 13px;
                }   
                '.$htmlId.' tbody tr:hover {
                  background-color: #e5f7fe;
                }
                '.$htmlId.' td {
                    padding-left: 2px !important;
                    padding-right: 5px !important;
                }   
                '.$htmlId.' {
                    border: 0.1rem solid #8a837f;
                }
            </style>   
            <script>
            require(["jquery","domReady"], function(jQuery,domReady){
                jQuery(document).ready(function () {
                    require(["Epicor_Dealerconnect/epicor/dealerconnect/js/drag"], function(drag) {
                        if (jQuery( "' . $htmlId . '" ).length ) {
                                jQuery("' . $combineBoth . '").tableDnD();
                                jQuery("' . $combineBoth . '  tr:even").addClass("altdeis");
                        }
                    });
                });
                jQuery("#addToEndBtn'.$this->getHtmlId().'").on("click",function() {
                  require(["Epicor_Dealerconnect/epicor/dealerconnect/js/drag"], function(drag) {
                       jQuery("' . $combineBoth . '").tableDnDUpdate();
                       jQuery("' . $combineBoth . '  tr:even").addClass("altdeis"); 
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