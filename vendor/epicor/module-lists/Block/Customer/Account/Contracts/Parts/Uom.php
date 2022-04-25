<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Contracts\Parts;

/**
 * Customer Orders list
 */
class Uom extends \Epicor\Common\Block\Generic\Listing {

    protected function _setupGrid() {
        $this->_controller = 'customer_account_contracts_parts_uom';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Uom');
    }

}
