<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\ResourceModel\DealerReminder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection  extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Epicor\Dealerconnect\Model\DealerReminder',
            'Epicor\Dealerconnect\Model\ResourceModel\DealerReminder'
        );
    }


}
