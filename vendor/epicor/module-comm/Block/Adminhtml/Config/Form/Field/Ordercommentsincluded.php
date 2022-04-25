<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Config\Form\Field;


class Ordercommentsincluded extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Form\Element\ErpaccountFactory
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Comm\Block\Adminhtml\Form\Element\ErpaccountFactory $commAdminhtmlFormElementErpaccountFactory,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }
    
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        if ($this->getBeforeElementHtml()) {
            $html .= '<label class="addbefore" for="' .
                $this->getHtmlId() .
                '">' .
                $this->getBeforeElementHtml() .
                '</label>';
        }
        $html .= '<select id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" ' . $this->serialize(
            $this->getHtmlAttributes()
        ) . $this->_getUiId() . '>' . "\n";

        $value = $element->getData('value');
        if (!is_array($value)) {
            if (!$this->checkP21System() && $value =="E") {
                $value ="B";
            }
            $value = [$value];
        }
        if ($values = $this->getValues()) {
            foreach ($values as $key => $option) {
                if (!is_array($option)) {
                    $html .= $this->_optionToHtml(['value' => $key, 'label' => $option], $value);
                } elseif (is_array($option['value'])) {
                    $html .= '<optgroup label="' . $option['label'] . '">' . "\n";
                    foreach ($option['value'] as $groupItem) {
                        $html .= $this->_optionToHtml($groupItem, $value);
                    }
                    $html .= '</optgroup>' . "\n"; 
                } else {
                    $html .= $this->_optionToHtml($option, $value);
                }
            }
        }
        $html .= '</select>' . "\n";
        if ($this->getAfterElementHtml()) {
            $html .= '<label class="addafter" for="' .
                $this->getHtmlId() .
                '">' .
                "\n{$this->getAfterElementHtml()}\n" .
                '</label>' .
                "\n";
        }
        return $html;
    }      
    
    protected function _optionToHtml($option, $selected)
    {
        if (is_array($option['value'])) {
            $html = '<optgroup label="' . $option['label'] . '">' . "\n";
            foreach ($option['value'] as $groupItem) {
                $html .= $this->_optionToHtml($groupItem, $selected);
            }
            $html .= '</optgroup>' . "\n";
        } else {
            $html = '<option value="' .$option['value']. '"';
            $html .= isset($option['title']) ? 'title="' . $option['title']. '"' : '';
            $html .= isset($option['style']) ? 'style="' . $option['style'] . '"' : '';
            if (in_array($option['value'], $selected)) {
                $html .= ' selected="selected"';
            }
            $html .= '>' . $option['label'] . '</option>' . "\n";
        }
        return $html;
    }   
    
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
            $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);
            // Disable element if value is inherited from other scope. Flag has to be set before the value is rendered.
            if ($element->getInherit() == 1 && $isCheckboxRequired) {
                $element->setDisabled(true);
            }
            $html = '<td class="label"><label for="' .
                $element->getHtmlId() . '"><span' .
                $this->_renderScopeLabel($element) . '>' .
                $element->getLabel() .
                '</span></label></td>';
            $html .= $this->_renderValue($element);

            if ($isCheckboxRequired) {
                $html .= $this->_renderInheritCheckbox($element);
            }
            $html .= $this->_renderHint($element);
            return $html;
    } 
    
    public function getValues() {
        $options = [
            'O' => 'Order Text',
            'C' => 'Carriage Text',
            'B' => 'Both',
            'E' => 'ERP will decide'
        ];
        
        if (!$this->checkP21System()) {
            unset($options['E']);
        }    
        return $options;        
    }
    
    public function checkP21System() {
        return $this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) =="p21";
    }    

    /**
     * Get the Html Id.
     *
     * @return string
     */
    public function getHtmlId()
    {
        return 'checkout_options_order_comments_included_in';
    }
    
    /**
     * Get the name.
     *
     * @return mixed
     */
    public function getName()
    {
        return 'groups[options][fields][order_comments_included_in][value]';
    }    
    
    protected function _getUiId($suffix = null)
    {
       return ' data-ui-id="select-groups-options-fields-order-comments-included-in-value"';
       
    } 
    

    /**
     * Get the Html attributes.
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return [
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'readonly',
            'tabindex',
            'data-form-part',
            'data-role',
            'data-action'
        ];
    }      
    
}