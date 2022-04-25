<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Access\Group;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Right extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_access_group_right', 'entity_id');
    }

}