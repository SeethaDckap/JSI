<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Account\Group\Currency\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Customer\Erpaccount\Currency', 'Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Currency');
    }

}
