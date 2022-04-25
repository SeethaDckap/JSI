<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Model\ResourceModel\Claimstatus;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Epicor\Dealerconnect\Model\Claimstatus', 'Epicor\Dealerconnect\Model\ResourceModel\Claimstatus');
    }
}