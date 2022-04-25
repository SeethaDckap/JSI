<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Approvals\Renderer;


class View extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $args = [
            'order_id' => $row->getData('entity_id'),
            'view_order_approval' => '1'
        ];
        $url = $this->getUrl('sales/order/view', $args);

        $historyData = '<input type="hidden" name="history_data['
            . $row->getData('entity_id') . ']" value="' . $row->getData('history_id') . '" />';
        $orderTotal = '<input type="hidden" name="order_grand_total['
            . $row->getData('entity_id') . ']" value="' . $row->getData('grand_total') . '" />';
        return '<a href="' . $url . '">view</a>' . $historyData . $orderTotal;
    }
}