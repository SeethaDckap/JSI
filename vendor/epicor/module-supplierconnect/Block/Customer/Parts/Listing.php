<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Parts;


/**
 * Parts list grid setup
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{
    const FRONTEND_RESOURCE = 'Epicor_Supplier::supplier_parts_read';

    protected function _setupGrid()
    {
        $this->_controller = 'customer_parts_listing';
        $this->_blockGroup = 'Epicor_Supplierconnect';
        $this->_headerText = __('Parts');
    }

}
