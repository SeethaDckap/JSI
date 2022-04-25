<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Lib\Varien\Data\Form\Element;


use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
/**
 * Custom array field display, displays an array of data like in the sytem config with add / removable rows
 *-
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
abstract class AbstractArray extends AbstractElement
{
    protected $_trackChanges = true;
    protected $_trackRowDelete = false;
    protected $_rowsContainIds = true;
    protected $_allowAdd = true;
    protected $_columns;
    protected $_data;
    protected $_loaded_models;
    protected  $_data_form_part = ''; 
    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var Mapping\SourceModelReader
     */
    protected $sourceModelReader;
    /*
     * @var Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Status
     */
    protected $erp_images_Status;
    /*
     * @var Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Stores
     */
    protected $erp_images_Stores;
    /*
     * @var Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Types
     */
    protected $erp_images_Types;
    
    protected abstract function getColumns();

    protected abstract function getRowData();

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Common\Lib\Varien\Data\Form\Element\Mapping\SourceModelReader $sourceModelReader,
        \Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Status  $erp_images_Status,
        //\Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Storeinfo $erp_images_Storeinfo,
        \Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Stores $erp_images_Stores,
        \Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Types $erp_images_Types,   
        array $data = []
    )
    {
        $this->commonHelper = $commonHelper;
        $this->sourceModelReader = $sourceModelReader;
        $this->erp_images_Status = $erp_images_Status;
        //$this->erp_images_Storeinfo = $erp_images_Storeinfo;
        $this->erp_images_Stores = $erp_images_Stores;
        $this->erp_images_Types = $erp_images_Types;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }


    /* public function __construct($attributes = array()) {
         parent::__construct($attributes);
     }*/

    public function getElementHtml() {
        $helper = $this->commonHelper;

        $columns = $helper->arrayToVarian($this->getColumns());
        $rows = $helper->arrayToVarian($this->getRowData());

        $table_id = str_replace(array('[', ']'), array('_', ''), $this->getName());

        $html = '';

        if ($this->_trackChanges) {
            $html .= '<input type="hidden" data-form-part="'.$this->_data_form_part.'" name="' . $this->getName() . '[origData]" value="' . $this->getValue() . '" />';
            $html .= ' <input type="hidden" data-form-part="'.$this->_data_form_part.'" id="' . $table_id . '_delete" name="' . $this->getName() . '[deleteRow]" value="" />';
        }
        if ($this->_trackRowDelete) {
            $html .= ' <input type="hidden" data-form-part="'.$this->_data_form_part.'" id="' . $table_id . '_delete" name="delete_' . $this->getName() . '" value="" />';
        }

        $html .= '<div class="data-grid" id="' . $table_id . '_holder">';
        $html .= '<table id="' . $table_id . '" class="border" cellspacing="0" cellpadding="0">';
        $html .= '  <thead>';
        $html .= '      <tr class="headings">';
        $cols = 1;
        foreach ($columns->getData() as $key => $field) {
            if ($field->getType() != 'hidden') {
                $html .= '<th class="data-grid-th">' . $field->getLabel() . '</th>';
                $cols++;
            }
        }
        $html .= '          <th></th>';
        $html .= '      </tr>';
        $html .= '  </thead>';
        $html .= '  <tbody>';
        foreach ($rows->getData() as $rowId => $row) {
            if ($this->_rowsContainIds) {
                $rowId = $row->getId();
            }
            $html .= '  <tr id="' . $table_id . '_row_' . $rowId . '">';
            foreach ($columns->getData() as $key => $field) {
                $name = $this->getName() . '[' . $rowId . '][' . $key . ']';
                $value = $row->getData($key);

                if ($field->getRenderer() && $field->getLabel()) {
                    
                    /** below commented code does not work in M2 **/
                   /*$rendererName = $field->getRenderer();
                    $renderer = new $rendererName(array('row_data' => $row));
                    $input = $renderer->_toHtml();
                    */
                    
                    $label = $field->getLabel();
                    switch($label){
                        case 'Types':
                            $input = $this->erp_images_Types->_generateHtml($row);
                            break;
                        case 'Stores':
                            $input = $this->erp_images_Stores->_generateHtml($row); 
                            break;
                         case 'Status':
                            $input = $this->erp_images_Status->_generateHtml($row); 
                             break;
                        default:
                            
                    } 
                    
                } else {
                    switch ($field->getType()) {
                        case 'select':
                        case 'multiselect':
                            $input = $this->buildSelect($field, $name, $value, $field->getType() == 'multiselect');
                            break;
                        case 'customselect':
                            $input = $this->buildCustomSelect($field, $name, $value);
                            break;
                        case 'static':
                            $input = $value;
                            break;
                        case 'checkbox':
                            $input = $this->buildCheckbox($field, $name, $value, $field->getType() == 'multiselect');
                            break;
                        default:
                            $input = '<input name="' . $name . '" class="input-text ' . $field->getClass() . '" type="' . $field->getType() . '" value="' . $value . '" />';
                            break;
                    }
                }
                if ($field->getType() != 'hidden')
                    $html .= '<td>' . $input . '</td>';
                else
                    $html .= $input;
            }

            $callback = $this->getDeleteCallback() ? $this->getDeleteCallback() . '(\'' . $table_id . '_row_' . $rowId . '\');' : '';

            $html .= '<td><button type="button" onclick="' . $table_id . '_array.removeRow(\'' . $table_id . '_row_' . $rowId . '\');' . $callback . 'return false;">Remove</button></td>';
            $html .= '  </tr>';
        }
        $html .= '  </tbody>';
        if ($this->_allowAdd) {
            $html .= '  <tfoot>';
            $html .= '      <tr>';
            $html .= '          <td colspan="' . $cols . '" style="text-align:right;"><button type="button" onclick="' . $table_id . '_array.addRow(\'' . $table_id . '\');return false;">Add</button></td>';
            $html .= '      </tr>';
            $html .= '  </tfoot>';
        }
        $html .= '</table>';

        $html .= '<script type="text/javascript">';

        if ($this->_allowAdd) {
            $html .= $table_id . '_template = \'<tr id="' . $table_id . '_row_#{id}">';

            foreach ($columns->getData() as $key => $field) {
                if ($this->_trackChanges) {
                    $name = $this->getName() . '[addRow][#{id}][' . $key . ']';
                } else {
                    $name = $this->getName() . '[#{id}][' . $key . ']';
                }
                $value = '';
                switch ($field->getType()) {
                    case 'select':
                    case 'multiselect':
                        $input = $this->buildSelect($field, $name, $value, $field->getType() == 'multiselect');
                        break;
                    case 'customselect':
                        $input = $this->buildCustomSelect($field, $name, $value);
                        break;
                    case 'static':
                        $input = $value;
                        break;
                    case 'checkbox':
                        $input = $this->buildCheckbox($field, $name, $field->getDefault(), $field->getType() == 'multiselect');
                        break;
                    default:
                        $input = '<input name="' . $name . '" class="input-text ' . $field->getClass() . '" type="' . $field->getType() . '" value="' . $value . '" />';
                        break;
                }
                if ($field->getType() != 'hidden')
                    $html .= '<td>' . $input . '</td>';
                else
                    $html .= $input;
            }

            $callback = $this->getDeleteCallback() ? $this->getDeleteCallback() . '(\\\'' . $table_id . '_row_#{id}\\\');' : '';

            $html .= '<td><button type="button" onclick="' . $table_id . '_array.removeRow(\\\'' . $table_id . '_row_#{id}\\\');' . $callback . 'return false;">Remove</button></td>';

            $html .= '</tr>\';' . "\n";
        } else {
            $html .= $table_id . '_template = \'\';' . "\n";
        }

        $html .= $table_id . '_array = new Epicor.arrayTableHandler("' . $table_id . '", ' . $table_id . '_template);';
        $html .= '</script>';
        $html .= '</div>';

        return $html;
    }

    private function buildSelect($field, $name, $value, $multiple = false) {
        $input = '';
        if ($multiple) {
            $input .= '<select name="' . $name . '[]" class="select" multiselect" multiple="multiple" size="5">';
            if (is_string($value)) {
                $value = explode(', ', $value);
            } else if ($value instanceof \Magento\Framework\DataObject) {
                $value = $value->getData();
            }
        } else {
            $input .= '<select name="' . $name . '" class="select">';
        }

        $data = $this->getToOptionArrayModel($field->getSourceModel(), $multiple);
        foreach ($data as $key => $option) {
            if (is_string($option) || $option instanceof \Magento\Framework\Phrase) {
                $option = array(
                    'label' => $option,
                    'value' => $key
                );
            }
            if (is_array($option) && is_array($option['value'])) {
                $input .= '<optgroup label="' . $option['label'] . '">';

                foreach ($option['value'] as $suboption) {
                    $input .= $this->buildOption($value, $suboption['value'], $suboption['label']);
                }

                $input .= '</optgroup>';
            } else {
                $input .= $this->buildOption($value, $option['value'], $option['label']);
            }
        }
        #<optgroup label=""></optgroup>
        $input .= '</select>';
        return $input;
    }

    private function buildCustomSelect($field, $name, $value) {
        $input = '<select name="' . $name . '" class="select">';
        $options = $field->getOptions()->getData();
        foreach ($options as $optVal => $optLabel) {
            if ($optVal == 'rowval') {
                if (!empty($value)) {
                    $input .= $this->buildOption($value, $value, $optLabel);
                }
            } else {
                $input .= $this->buildOption($value, $optVal, $optLabel);
            }
        }

        $input .= '</select>';
        return $input;
    }

    private function buildOption($rowValue, $value, $label) {

        $option = '<option value="' . $value . '" ';

        if ((is_array($rowValue) && in_array($value, $rowValue) ) || $rowValue == $value) {
            $option .= 'selected="selected" ';
        }

        $option .= '>' . $label . '</option>';
        return $option;
    }

    private function buildCheckbox($field, $name, $value) {
        $input = '<input name="' . $name . '" class="input-text" type="checkbox" value="1"';

        if ($field->getDisabled()) {
            $input .= ' disabled="disabled"';
        }

        if ($value) {
            $input .= ' checked="checked"';
        }

        $input .= ' />';

        if ($field->getDisabled()) {
            $input .= '<input name="' . $name . '" class="input-text" type="hidden" value="' . $value . '"/>';
        }
        return $input;
    }

    private function getToOptionArrayModel($model, $multiple) {
        if (!isset($this->_loaded_models[$model])) {
            $this->_loaded_models[$model] = $this->sourceModelReader->getModel($model)->toOptionArray($multiple);
        }

        return $this->_loaded_models[$model];
    }
}