<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Model\ResourceModel\Managedashboard;

/**
 * Rules collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Epicor\Common\Model\Managedashboard::class,
            \Epicor\Common\Model\ResourceModel\Managedashboard::class
        );
    }
}
