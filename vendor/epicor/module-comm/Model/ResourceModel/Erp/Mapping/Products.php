<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Erp\Mapping;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Products extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ecc_erp_mapping_products', 'id');
    }
}
