<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Widget\Grid\Column\Renderer;


/**
 * List admin actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Mastershopper extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Render master shopper column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $tick = ' <img src="' . $this->getViewFileUrl('Epicor_Common::epicor/common/images/success_msg_icon.gif') . '" alt="Yes" /> ';
        $cross = ' <img src="' . $this->getViewFileUrl('Epicor_Common::epicor/common/images/cancel_icon.gif') . '" alt="No" /> ';

        switch ($row->getMasterShopper()) {
            case 'y':
                $display = $tick;
                break;
            case 'n':
                $display = $cross;
                break;
        }

        return $display;
    }

}
