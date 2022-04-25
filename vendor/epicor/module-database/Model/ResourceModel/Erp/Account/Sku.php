<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Erp\Account;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Sku extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_erp_account_sku', 'entity_id');
    }

}