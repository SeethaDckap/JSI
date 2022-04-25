<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model\Eav\Attribute\Data\Customer;

class AccessRoles extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    protected $request;
    protected $accessRoleCustomer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\AccessRight\Model\RoleModel\Customer $accessRoleCustomer,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->request = $request;
        $this->accessRoleCustomer = $accessRoleCustomer;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        parent::__construct($eavAttrEntity);
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->accessRoleCustomer->getAccessRolesOptionsFrontEnd($this->getCustomerId(), $this->getParentErpAccount($this->getCustomerId()), false, 'admin');
    }

    public function getCustomerId()
    {
        $customerId = $this->request->getParam('id');
        if ($customerId == "") {
            $customerId = isset($this->request->getParam('customer')['entity_id']) ? $this->request->getParam('customer')['entity_id'] : "";
        }
        return $customerId;
    }

    public function getParentErpAccount($customerId)
    {
        $customerCollection = $this->customerFactory->create()->load($customerId);
        $erpAccounts = $customerCollection->getErpAcctCounts();
        if ($this->customerSession->getMasqueradeAccountId()) {
            return $this->customerSession->getMasqueradeAccountId();
        }
        return (count($erpAccounts) == 0) ? 0 : ((count($erpAccounts) == 1) ? $customerCollection->getEccErpaccountId() : "M");
    }

}
