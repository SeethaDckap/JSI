<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Salesreps;


/**
 * Sales Rep Account Sales Rep List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    protected function _setupGrid()
    {
        $this->_controller = 'account_manage_salesreps_listing';
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_headerText = __('Sales Reps');
    }

}
