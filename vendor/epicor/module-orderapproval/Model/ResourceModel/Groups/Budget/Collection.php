<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel\Groups\Budget;

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
    /**
     * Construct.
     */
    public function _construct()
    {
        $this->_init(
            'Epicor\OrderApproval\Model\Groups\Budget',
            'Epicor\OrderApproval\Model\ResourceModel\Groups\Budget'
        );
    }

}
