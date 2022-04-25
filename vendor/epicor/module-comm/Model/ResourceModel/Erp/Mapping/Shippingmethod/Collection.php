<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingmethod;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Shippingmethod\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Erp\Mapping\Shippingmethod', 'Epicor\Comm\Model\ResourceModel\Erp\Mapping\Shippingmethod');
    }

}
