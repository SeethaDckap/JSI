<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Catalog\Category;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Catalog\Category\Entity\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Erp\Catalog\Category', 'Epicor\Comm\Model\ResourceModel\Erp\Catalog\Category');
    }

}
