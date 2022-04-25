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
class Textarea extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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

        $html .= '<textarea name="textarea_' . $row->getId() . '" readonly="true" rows="5" cols="50">' . $row->getStatusHelp() . '</textarea>';

        return $html;
    }

}
