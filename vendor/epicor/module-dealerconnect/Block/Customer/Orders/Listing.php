<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Customer\Orders;


/**
 * Dealer Orders list
 */
class Listing extends \Epicor\Customerconnect\Block\Customer\Orders\Listing
{
    const FRONTEND_RESOURCE = 'Dealer_Connect::dealer_orders_read';
    const ACCESS_MESSAGE_DISPLAY = TRUE;

    protected function _setupGrid()
    {
        $this->_controller = 'customer_orders_listing';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_headerText = __('Orders');
    }
}
