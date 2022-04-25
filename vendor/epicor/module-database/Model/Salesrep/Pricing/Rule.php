<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\Salesrep\Pricing;

use Magento\Framework\Model\AbstractModel;

class Rule extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\ResourceModel\Salesrep\Pricing\Rule');
    }

}