<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Account\Group\Hierarchy\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Erp\Customer\Group\Hierarchy', 'Epicor\Comm\Model\ResourceModel\Erp\Customer\Group\Hierarchy');
    }

}
