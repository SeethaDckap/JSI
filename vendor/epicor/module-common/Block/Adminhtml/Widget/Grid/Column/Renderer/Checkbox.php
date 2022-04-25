<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Filesize grid column renderer. renders a file size in human readable format
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{



    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $is_default = $row->getIsDefault();
        $checked = ($row->getIsDefault()) ? 'checked' : '';
        if ($is_default) {
            $html .= '<input type="checkbox" disabled readonly value=' . "'" . $row->getIsDefault() . "'" . "checked=" . $checked . "></input>";
        } else {
            $html .= '<input type="checkbox" disabled readonly value=' . "'" . $row->getIsDefault() . "'" . "></input>";
        }
        return $html;
    }

}
