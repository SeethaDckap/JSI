<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Payment\Collection
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Comm\Model\Erp\Mapping\Payment', 'Epicor\Comm\Model\ResourceModel\Erp\Mapping\Payment');
    }

}
