<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Dealergroups\Erp;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Account extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_dealer_groups_accounts', 'id');
    }

}