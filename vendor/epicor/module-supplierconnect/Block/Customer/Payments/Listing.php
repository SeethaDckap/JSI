<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Payments;


/**
 * Customer Payments list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{
    protected function _setupGrid()
    {
        $this->_controller = 'customer_payments_listing';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Payments');
    }
}
