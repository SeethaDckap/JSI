<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Lists\Address;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Restriction extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_list_address_restriction', 'id');
    }

}