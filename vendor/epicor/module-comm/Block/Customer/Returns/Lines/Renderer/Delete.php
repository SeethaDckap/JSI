<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Renderer;


/**
 * Return line delete column renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Comm_Model_Customer_ReturnModel_Line */

        $checked = $row->getToBeDeleted() == 'Y' ? ' checked="checked"' : '';

        $html = '<input type="checkbox" class="return_line_delete" name="lines[' . $row->getUniqueId() . '][delete]"' . $checked . ' />';

        return $html;
    }

}
