<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Invoices\Details;


class Lines extends \Epicor\Common\Block\Generic\Listing
{

    protected function _setupGrid()
    {
        $this->_controller = 'customer_invoices_details_lines';
        $this->_blockGroup = 'Epicor_Customerconnect';
        $this->_headerText = __('Lines');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

}
