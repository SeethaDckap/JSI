<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\ResourceModel\Location\Relatedlocations;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Epicor\Comm\Model\Location\Relatedlocations', 'Epicor\Comm\Model\ResourceModel\Location\Relatedlocations');
    }
}