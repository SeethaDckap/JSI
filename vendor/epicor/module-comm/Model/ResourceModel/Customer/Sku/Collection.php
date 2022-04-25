<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Customer\Sku;


class Collection extends \Epicor\Database\Model\ResourceModel\Erp\Account\Sku\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Customer\Sku', 'Epicor\Comm\Model\ResourceModel\Customer\Sku');
    }

    protected function _initSelect()
    {
            $this->addFilterToMap('sku', 'main_table.sku');
            parent::_initSelect();
    }
    public function getProductSelect()
    {
        $this->getSelect()->joinLeft(
                array('table_alias' => $this->getTable('ecc_erp_account')), 'main_table.customer_group_id = table_alias.entity_id', array('table_alias.erp_code')
            )
            ->joinLeft(
                array('product_table' => $this->getTable('catalog_product_entity')), 'main_table.product_id = product_table.entity_id', array('product_sku' => 'product_table.sku'));
    }

}
