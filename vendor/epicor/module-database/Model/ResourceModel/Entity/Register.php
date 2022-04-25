<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Entity;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Register extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_entity_register', 'row_id');
    }

}