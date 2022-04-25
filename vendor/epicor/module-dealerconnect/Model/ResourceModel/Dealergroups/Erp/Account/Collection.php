<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\Erp\Account;


/**
 * Model Collection Class for Dealer Account
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Dealergroups\Erp\Account\Collection
{
    public function _construct()
    {
        $this->_init('Epicor\Dealerconnect\Model\Dealergroups\Erp\Account','Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\Erp\Account');
    }

}
