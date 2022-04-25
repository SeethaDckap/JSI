<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer as WidgetAbstractRenderer;

class StartDate extends WidgetAbstractRenderer
{

    /**
     * Renders grid column End date
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $startDate = strtotime($row->getData('start_date'));

        return date('Y-m-d', $startDate);
    }
}