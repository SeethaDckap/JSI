<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\Dealergroups\Erp;

use Magento\Framework\Model\AbstractModel;

class Account extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\ResourceModel\Dealergroups\Erp\Account');
    }

}