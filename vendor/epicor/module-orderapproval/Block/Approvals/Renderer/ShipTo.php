<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Approvals\Renderer;

use  Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer as WidgetAbstractRenderer;

class ShipTo extends WidgetAbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $row->getData('s_firstname') . ' ' . $row->getData('customer_lastname');
    }
}
