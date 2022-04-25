<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\ResourceModel\SupplierReminder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection  extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Epicor\Supplierconnect\Model\SupplierReminder',
            'Epicor\Supplierconnect\Model\ResourceModel\SupplierReminder'
        );
    }


}
