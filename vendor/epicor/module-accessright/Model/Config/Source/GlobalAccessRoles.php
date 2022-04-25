<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\Config\Source;


class GlobalAccessRoles
{
    const ERP_ACCOUNT_LINK_TYPE = \Epicor\AccessRight\Model\RoleModel::ERP_ACC_LINK_TYPE_B2B;

    /**
     * @var Access Roles Model
     */
    protected $_accessRolesFactory;

    public function __construct(
        \Epicor\AccessRight\Model\RoleModelFactory $accessRolesFactory
    ) {
        $this->_accessRolesFactory = $accessRolesFactory;
    }

    public function toOptionArray()
    {
        $_options = [];
        $accessRoles = $this->_accessRolesFactory->create();
        $roles = $accessRoles->getRoles(static::ERP_ACCOUNT_LINK_TYPE);
        $_options = $this->getOptionsArray($roles);
        return $_options;
    }

    protected function getOptionsArray($roles)
    {
        $_roles = [];
        foreach ($roles as $role) {
            $_roles[] = [
                'value' => $role['id'],
                'label' => $role['title'],
                'autoassign' => $role['auto_assign']
            ];
        }
        return $_roles;
    }
}
