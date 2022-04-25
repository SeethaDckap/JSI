<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping\Country;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Country\Collection
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Erp\Mapping\Country', 'Epicor\Comm\Model\ResourceModel\Erp\Mapping\Country');
    }

}
