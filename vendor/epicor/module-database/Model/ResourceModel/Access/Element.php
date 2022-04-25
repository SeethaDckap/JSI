<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Access;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Element extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_access_element', 'id');
    }

}