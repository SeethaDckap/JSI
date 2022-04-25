<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Erp\Catalog\Category;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Entity extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_erp_catalog_category_entity', 'entity_id');
    }

}