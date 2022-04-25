<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DealerReminder extends AbstractDb
{

    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('ecc_dealer_reminder', 'id');
    }
}
