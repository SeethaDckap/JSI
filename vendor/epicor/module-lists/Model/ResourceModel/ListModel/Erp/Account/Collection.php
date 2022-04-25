<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account;


/**
 * Model Collection Class for List Erp Account
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Erp\Account\Collection
{
    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Erp\Account','Epicor\Lists\Model\ResourceModel\ListModel\Erp\Account');
    }

}
