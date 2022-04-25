<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
//M1 > M2 Translation Begin (Rule 35)
namespace Epicor\Comm\Model\ResourceModel\Grid;


class Collection extends \Magento\Customer\Model\ResourceModel\Grid\Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        /*$this->getSelect()->joinLeft(
            ['cc' => $this->getTable('ecc_erp_account')],
            'cc.entity_id=main_table.ecc_erpaccount_id',
            ['customer_erp_code' => 'erp_code', 'customer_company' => 'company', 'customer_short_code' => 'short_code']
        );*/

        return $this;
    }
}
//M1 > M2 Translation End