<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders;


/**
 * Customer Orders list
 */
class Create extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_confirm_new_po_read';
    
    protected function _setupGrid()
    {
        $this->_controller = 'customer_orders_create';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('New Purchase Orders');
    }

}
