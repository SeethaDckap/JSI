<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\ResourceModel\RoleModel\Customer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
/**
 * Model Collection Class for Role Customer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Epicor\AccessRight\Model\RoleModel\Customer','Epicor\AccessRight\Model\ResourceModel\RoleModel\Customer');
    }

}
