<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping\Claimstatus;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Mapping\Claimstatus\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Erp\Mapping\Claimstatus', 'Epicor\Comm\Model\ResourceModel\Erp\Mapping\Claimstatus');
    }

}
