<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel\Groups;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Model Resource Class for Groups Link
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Ecc Team
 */
class Link extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_approval_group_link', 'id');
    }
}
