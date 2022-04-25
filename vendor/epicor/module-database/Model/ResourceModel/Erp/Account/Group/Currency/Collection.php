<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Erp\Account\Group\Currency;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\Erp\Account\Group\Currency', 'Epicor\Database\Model\ResourceModel\Erp\Account\Group\Currency');
    }

}