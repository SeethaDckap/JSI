<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Listing\Contracts;

/**
 * Customer Invoices list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_contracts_read';

    const ACCESS_MESSAGE_DISPLAY = TRUE;

    protected function _setupGrid() {
        $this->_controller = 'customer_listing_contracts_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Contracts');
    }

}
