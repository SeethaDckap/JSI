<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Erp\Mapping\Reasoncode;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Accounts extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_erp_mapping_reasoncode_accounts', 'id');
    }

}