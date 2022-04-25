<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Approvals\Renderer;

use  Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer as WidgetAbstractRenderer;

class Requestor extends WidgetAbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $row->getData('customer_firstname') . ' ' . $row->getData('customer_lastname');
    }
}
