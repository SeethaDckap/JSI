<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Customer extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_quote_customer', 'entity_id');
    }

}