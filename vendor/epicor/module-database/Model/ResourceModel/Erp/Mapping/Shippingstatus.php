<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Erp\Mapping;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Shippingstatus extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_erp_mapping_shippingstatus', 'id');
    }

}