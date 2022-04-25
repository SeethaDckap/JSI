<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Approvals\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer as WidgetAbstractRenderer;

class Reject extends WidgetAbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return '<input type="checkbox" name="rejected[]" value="' .
            $row->getId() . '" id="order_approval_reject_' .
            $row->getId() . '" class="order_approval_reject" '  . '/>';
    }
}