<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class File extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_file', 'id');
    }

}