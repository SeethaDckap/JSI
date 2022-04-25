<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Orders;


/**
 * Customer Orders list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{


    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_orders_read';

    const ACCESS_MESSAGE_DISPLAY = TRUE;

    protected function _setupGrid()
    {
        $this->_controller = 'customer_orders_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Orders');
    }

}
