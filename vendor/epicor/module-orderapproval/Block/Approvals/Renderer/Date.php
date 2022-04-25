<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Approvals\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer as WidgetAbstractRenderer;

class Date extends WidgetAbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $rowDate = $row->getData('created_at');
        return $this->formatDate($rowDate);
    }
}
