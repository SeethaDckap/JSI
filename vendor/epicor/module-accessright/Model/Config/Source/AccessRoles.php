<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model\Config\Source;

class AccessRoles extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean {

    protected $request;
    protected $accessroleErpAccount;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\AccessRight\Model\RoleModel\Erp\Account $accessroleErpAccount
    ) {
        $this->request = $request;
        $this->accessroleErpAccount = $accessroleErpAccount;
        parent::__construct($eavAttrEntity);
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions() {

        $accessRoleOptions = $this->accessroleErpAccount->getAccessRolesOptions($this->getErpAccountId());    
        return $accessRoleOptions;
    }

    public function getErpAccountId() {
        return $this->request->getParam('id');
    }
}
