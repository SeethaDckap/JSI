<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Customer\Group;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Account\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Erp\Customer\Group', 'Epicor\Comm\Model\ResourceModel\Erp\Customer\Group');
    }

}
