<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Model Collection Class for Order Approval Groups Erp Account
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Ecc Team
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            'Epicor\OrderApproval\Model\Groups\Erp\Account',
            'Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account'
        );
    }

}
