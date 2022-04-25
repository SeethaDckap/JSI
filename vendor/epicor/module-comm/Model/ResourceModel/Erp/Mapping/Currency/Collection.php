<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping\Currency;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Currency\Collection
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Erp\Mapping\Currency', 'Epicor\Comm\Model\ResourceModel\Erp\Mapping\Currency');
    }

}
