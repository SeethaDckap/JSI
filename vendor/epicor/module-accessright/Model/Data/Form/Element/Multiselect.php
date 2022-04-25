<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Form select element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Epicor\AccessRight\Model\Data\Form\Element;

class Multiselect extends \Magento\Framework\Data\Form\Element\Multiselect
{

    /**
     * @param array $option
     * @param array $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $html = '<option value="' . $this->_escape($option['value']) . '"';
        $html .= ' class="access-roles"';
        $html .= isset($option['autoassign']) ? 'autoassign="' . $this->_escape($option['autoassign']) . '"' : '';
        $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
        $html .= isset($option['style']) ? 'style="' . $option['style'] . '"' : '';
        if (in_array((string)$option['value'], $selected)) {
            $html .= ' selected="selected"';
        }
        if(isset($option['autoassign']) && $option['autoassign'] == 1){
           $html .= ' selected="selected"'; 
        }
        $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        return $html;
    }
}
