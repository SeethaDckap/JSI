<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Model Resource Class for Groups
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 */
class Import extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_list_mass_action', 'id');
    }
}
