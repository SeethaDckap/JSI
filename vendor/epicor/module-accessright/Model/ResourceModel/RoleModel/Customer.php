<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\ResourceModel\RoleModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
/**
 * Model Resource Class for Role Customer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Customer extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_access_role_customer', 'id');
    }
}
