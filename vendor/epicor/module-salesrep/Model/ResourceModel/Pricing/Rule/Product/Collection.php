<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\Product;


class Collection extends \Epicor\Database\Model\ResourceModel\Salesrep\Pricing\Rule\Product\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\SalesRep\Model\Pricing\Rule\Product', 'Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\Product');
    }

}
