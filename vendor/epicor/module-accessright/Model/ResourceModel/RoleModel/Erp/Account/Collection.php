<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
/**
 * Model Collection Class for Role Erp Account
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Epicor\AccessRight\Model\RoleModel\Erp\Account','Epicor\AccessRight\Model\ResourceModel\RoleModel\Erp\Account');
    }

}
