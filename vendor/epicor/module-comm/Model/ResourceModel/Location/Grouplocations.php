<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Location;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
/**
 * Group Locations resource model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Grouplocations extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_location_grouplocations', 'id');
    }
}
