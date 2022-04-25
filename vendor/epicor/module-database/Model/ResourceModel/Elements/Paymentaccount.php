<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\ResourceModel\Elements;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Paymentaccount extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_elements_paymentaccount', 'entity_id');
    }

}