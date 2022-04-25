<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ErpAccountBudget extends AbstractDb
{
    /**
     * return void
     */
    protected function _construct()
    {
        $this->_init('ecc_erp_account_budget', 'id');
    }
}