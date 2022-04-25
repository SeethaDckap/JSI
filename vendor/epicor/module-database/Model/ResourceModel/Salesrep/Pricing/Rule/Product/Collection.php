<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Salesrep\Pricing\Rule\Product;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\Salesrep\Pricing\Rule\Product', 'Epicor\Database\Model\ResourceModel\Salesrep\Pricing\Rule\Product');
    }

}