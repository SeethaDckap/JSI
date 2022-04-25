<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping\Orderstatus;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Orderstatus\Collection
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Erp\Mapping\Orderstatus', 'Epicor\Comm\Model\ResourceModel\Erp\Mapping\Orderstatus');
    }

}
