<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\ResourceModel\Location\Product\StoreGroup;

class Collection extends \Epicor\Database\Model\ResourceModel\Location\Product\Storegroup\Collection
{
    protected function _construct()
    {
        $this->_init('Epicor\Comm\Model\Location\Product\Storegroup', 'Epicor\Comm\Model\ResourceModel\Location\Product\Storegroup');
    }
}