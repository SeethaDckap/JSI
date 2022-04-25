<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Location\Product;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Currency extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_location_product_currency', 'id');
    }

}