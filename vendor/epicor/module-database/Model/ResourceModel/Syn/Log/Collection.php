<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Syn\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\Syn\Log', 'Epicor\Database\Model\ResourceModel\Syn\Log');
    }

}