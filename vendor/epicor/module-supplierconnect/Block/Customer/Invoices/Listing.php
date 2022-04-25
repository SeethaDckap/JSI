<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Invoices;


/**
 * Customer Invoices list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_invoices_read';

    protected function _setupGrid()
    {
        $this->_controller = 'customer_invoices_listing';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Invoices');
    }
}
