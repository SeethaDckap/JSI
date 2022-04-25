<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Lists\Product;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Price extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_list_product_price', 'id');
    }

}