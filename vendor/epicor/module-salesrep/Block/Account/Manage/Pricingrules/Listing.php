<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Pricingrules;


/**
 * Sales Rep Account Pricing Rules List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Listing extends \Epicor\Common\Block\Generic\Listing
{

    protected function _setupGrid()
    {
        $this->_controller = 'account_manage_pricingrules_listing';
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_headerText = __('Pricing Rules');
    }

}
