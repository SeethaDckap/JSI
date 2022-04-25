<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DataMapping extends AbstractDb
{

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('ecc_erp_mapping_datamapping', 'id');
    }
}
