<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel\Groups\Link;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Model Collection Class for Groups Link
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor ECC Team
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\Groups\Link',
            'Epicor\OrderApproval\Model\ResourceModel\Groups\Link');
    }

}
