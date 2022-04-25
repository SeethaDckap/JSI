<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Access\Right\Element;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\Access\Right\Element', 'Epicor\Database\Model\ResourceModel\Access\Right\Element');
    }

}