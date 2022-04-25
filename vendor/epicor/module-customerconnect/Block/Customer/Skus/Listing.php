<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Skus;


class Listing extends \Epicor\Common\Block\Generic\Listing
{ // Mage_Adminhtml_Block_Widget_Grid_Container {//

    const FRONTEND_RESOURCE = 'Epicor_Customerconnect::customerconnect_account_skus_read';

    const ACCESS_MESSAGE_DISPLAY = TRUE;

    protected function _setupGrid()
    {
        $this->_controller = 'customer_skus_listing';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('SKUs');
    }

}
