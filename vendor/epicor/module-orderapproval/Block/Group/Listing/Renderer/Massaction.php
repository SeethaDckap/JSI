<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group\Listing\Renderer;

class Massaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Massaction
{
    /**
     * @param string $value
     * @param bool $checked
     * @return string
     * @throws \Exception
     */
    protected function _getCheckboxHtml($value, $checked)
    {
        if ($this->getDisabled()) {
            $value = 'disabled';
        }
        $id = 'id_' . random_int(0, 999);
        $html = '<label class="data-grid-checkbox-cell-inner" for="' . $id . '">';
        $html .= '<input type="checkbox" ' . $this->getDisabled() . ' name="' . $this->getColumn()->getName() . '" ';
        $html .= 'id="' . $id . '" data-role="select-row"';
        $html .= 'value="' . $this->escapeHtml($value) . '" class="admin__control-checkbox"' . $checked . '/>';
        $html .= '<label for="' . $id . '"></label></label>';
        return $html;
    }
}