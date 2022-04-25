<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Recentpurchases;

/**
 * Customer Recent Purchase list
 */
class Listing extends \Epicor\Common\Block\Generic\Listing {

    protected function _setupGrid() {
        $this->_controller = 'customer_recentpurchases_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Products');
    }

}
