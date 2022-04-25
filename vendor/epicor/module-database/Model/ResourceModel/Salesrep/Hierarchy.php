<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Salesrep;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Hierarchy extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_salesrep_hierarchy', 'id');
    }

}