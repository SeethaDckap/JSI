<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\Store;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Account\Address\Store\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Customer\Erpaccount\Address\Store', 'Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Address\Store');
    }

}
