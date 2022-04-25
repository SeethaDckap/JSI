<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Renderer;


class Contractquantities extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $list = array('Min Order Qty' => 'min_order_qty', 'Max Order Qty' => 'max_order_qty', 'Contract Qty' => 'qty');
        $quantitiesList = array('min_order_qty', 'max_order_qty', 'qty');
        $html = '';
        foreach ($quantitiesList as $quantity) {
            $html .= array_search($quantity, $list) . ' : ' . floatval($row[$quantity]) . '<br/>';
        }

        return $html;
    }

}
