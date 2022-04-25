<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * return void
     */
    public function _construct()
    {
        $this->_init(
            'Epicor\OrderApproval\Model\ErpAccountBudget',
            'Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget'
        );
    }
}