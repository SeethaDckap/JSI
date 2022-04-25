<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Location\Link;


/**
 * Location link collection model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Location\Link\Collection
{

    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Location\Link', 'Epicor\Comm\Model\ResourceModel\Location\Link');
    }

    public function joinErpAccountInfo()
    {
        $table = $this->getTable('ecc_erp_account');

        $this->getSelect()->joinLeft(array('cc' => $table), 'cc.entity_id=main_table.entity_id', array('account_name' => 'name', 'account_number' => 'account_number'), null, 'left');
        $this->getSelect()->group('main_table.entity_id');
    }

}
