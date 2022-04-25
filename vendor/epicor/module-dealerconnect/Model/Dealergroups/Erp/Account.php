<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Dealergroups\Erp;


/**
 * Model Class for Dealer Group Account
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 * 
 * @method string getErpAccountId()
 * 
 * @method string setErpAccountId()
 */
class Account extends \Epicor\Database\Model\Dealergroups\Erp\Account
{

    public function _construct()
    {
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\Dealergroups\Erp\Account');
    }

}
