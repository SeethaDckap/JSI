<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Erp;


/**
 * Model Class for List Erp Account
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 * 
 * @method string getListId()
 * @method string getErpAccountId()
 * 
 * @method string setListId()
 * @method string setErpAccountId()
 */
class Account extends \Epicor\Database\Model\Lists\Erp\Account
{

    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account');
    }

}
