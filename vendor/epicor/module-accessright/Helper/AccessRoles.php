<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Helper;

class AccessRoles extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Epicor\AccessRight\Model\Eav\Attribute\Data\Customer\AccessRoles
     */
    protected $accessRoles;
    /**
     *
     * @var \Epicor\AccessRight\Model\RoleModel\Customer $accessRoleCustomer
     */
    protected $accessRoleCustomer;

    /**
     * 
     * @param \Epicor\AccessRight\Model\Eav\Attribute\Data\Customer\AccessRoles $accessRoles
     * @param \Epicor\AccessRight\Model\RoleModel\Customer $accessRoleCustomer
     */
    public function __construct(
        \Epicor\AccessRight\Model\Eav\Attribute\Data\Customer\AccessRoles $accessRoles,
        \Epicor\AccessRight\Model\RoleModel\Customer $accessRoleCustomer
    ) {
        $this->accessRoles = $accessRoles;
        $this->accessRoleCustomer = $accessRoleCustomer;
    }
    
    public function getAccessRoles($customerId, $erpAccountId) {
        return $this->accessRoleCustomer->getAccessRolesOptionsFrontEnd($customerId, $erpAccountId, false, 'admin');
    }

}