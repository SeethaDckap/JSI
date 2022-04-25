<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Comm\Model\Location\Product;


class Storegroup extends \Epicor\Database\Model\Location\Product\Storegroup
{
    protected function _construct()
    {
        $this->_init('Epicor\Comm\Model\ResourceModel\Location\Product\Storegroup');
    }

}