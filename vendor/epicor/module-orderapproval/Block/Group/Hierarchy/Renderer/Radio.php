<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group\Hierarchy\Renderer;


class Radio extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $values = $this->getColumn()->getValues();
        $value = $row->getData($this->getColumn()->getIndex());
        if (is_array($values)) {
            $checked = in_array($value, $values) ? ' checked="checked"' : '';
        } else {
            $checked = $value === $this->getColumn()->getValue() ? ' checked="checked"' : '';
        }

        $disabled = '';
        $disabledValues = $this->getColumn()->getDisabledValues();
        if (is_array($disabledValues)) {
            $disabled = in_array($value, $disabledValues) ? ' disabled="disabled"' : '';
        }

        $html = '<input type="radio" name="' . $this->getColumn()->getHtmlName() . '" ';
        $html .= $disabled;
        $html .= ' value="' . $row->getId() . '" class="radio"' . $checked . '/>';
        return $html;
    }
}