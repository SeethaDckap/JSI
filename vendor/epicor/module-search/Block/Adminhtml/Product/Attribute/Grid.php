<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Search\Block\Adminhtml\Product\Attribute;

class Grid extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid
{

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumn('ecc_created_by', array(
            'header' => __('Created by STK?'),
            'sortable' => true,
            'index' => 'ecc_created_by',
            'type' => 'options',
            'options' => array(
                'STK' => __('Yes'),
                'N' => __('No'),
            ),
            'align' => 'center',
            ), 'is_filterable');
    }
}