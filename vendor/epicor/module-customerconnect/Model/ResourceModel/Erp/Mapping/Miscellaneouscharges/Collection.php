<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Epicor\Customerconnect\Model\Erp\Mapping\Miscellaneouscharges', 'Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges');
    }

}
