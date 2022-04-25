<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\Customer\ReturnModel;

use Magento\Framework\Model\AbstractModel;

class Line extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\ResourceModel\Customer\ReturnModel\Line');
    }

}