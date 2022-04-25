<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model\ResourceModel\Import;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Model Resource Class for Contracts
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            'Epicor\Lists\Model\Import',
            'Epicor\Lists\Model\ResourceModel\Import'
        );
    }
}
