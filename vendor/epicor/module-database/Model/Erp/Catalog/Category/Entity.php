<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\Erp\Catalog\Category;

use Magento\Framework\Model\AbstractModel;

class Entity extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\ResourceModel\Erp\Catalog\Category\Entity');
    }

}