<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage;


/**
 * Sales Rep Account Hierarchy Parents List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Parents extends \Epicor\Common\Block\Generic\Listing
{

    protected function _setupGrid()
    {
        $this->_controller = 'account_manage_parents';
        $this->_blockGroup = 'Epicor_SalesRep';
        $this->_headerText = __('Parent Accounts');
    }
    
    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    } 

}
