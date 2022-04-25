<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account;


/**
 * Setting button for adding new List 
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Deliveryaddress extends \Epicor\Common\Block\Generic\Listing
{

    protected function _setupGrid()
    {
        $this->_controller = 'customer_account_deliveryaddress';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = ''; //__('Delivery Addresses');
        $this->removeButton('add');
    }

    protected function _postSetup()
    {
        $this->setBoxed(true);
        parent::_postSetup();
    }

}
