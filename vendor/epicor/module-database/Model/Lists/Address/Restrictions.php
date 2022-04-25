<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\Lists\Address;

use Magento\Framework\Model\AbstractModel;

class Restrictions extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\ResourceModel\Lists\Address\Restrictions');
    }
}