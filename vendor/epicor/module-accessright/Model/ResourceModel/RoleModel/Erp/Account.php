<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
/**
 * Model Resource Class for Role Erp Account
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class Account extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_access_role_erp_account', 'id');
    }

}
