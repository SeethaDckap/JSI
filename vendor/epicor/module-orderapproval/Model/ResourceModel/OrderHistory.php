<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Model Resource Class for Groups
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 */
class OrderHistory extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_approval_order_history', 'id');
    }

    public function massInsert($data)
    {
        $totalRows = $this->getConnection()
            ->insertMultiple(
                $this->getTable('ecc_approval_order_history'),
                $data
            );

        return $totalRows;
    }
}
