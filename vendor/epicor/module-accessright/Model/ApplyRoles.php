<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model;

class ApplyRoles
{


    protected $isAccessEnabled = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\AccessRight\Model\RoleModelFactory
     */
    protected $rolesRoleModel;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    public function __construct(
        \Epicor\AccessRight\Model\RoleModel $rolesRoleModel,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory
    )
    {
        $this->rolesRoleModel = $rolesRoleModel;
        $this->customerSession = $customerSession;
        $this->_scopeConfig = $scopeConfig;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
    }

    public function isAccessRigtsEnabled()
    {
        if ($this->isAccessEnabled === null) {
            $this->isAccessEnabled = $this->_scopeConfig->isSetFlag('epicor_access_control/global/enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $this->isAccessEnabled;
    }

    public function getEccErpaccountId($customer, $ignoremasqurade = false)
    {

        $erpAccountId = $customer->getEccErpaccountId();
        if ($customer->getEccErpAccountType() == "supplier") {
            if ($customer->getSupplierErpAccount()) {
                $erpAccountId = $customer->getEccSupplierErpaccountId();
            }
        }
        if (!$ignoremasqurade) {
            if ($this->customerSession->getMasqueradeAccountId()) {
                $erpAccountId = $this->customerSession->getMasqueradeAccountId();
            }
        }
        return $erpAccountId;
    }

    public function getRoleByCustomer($customer)
    {
        $id = false;
        $customerId = $customer->getId();
        $erpAccountId = $this->getEccErpaccountId($customer, true);
        if ($customerId) {
            $id = $this->rolesRoleModel->getRoleAppliedByCustomer($customer, $erpAccountId);
        }

        return $id;
    }

    public function getRoleByDefaultErp()
    {
        $id = $this->getDefaultGlobalRole();
        return $id;
    }

    public function getRoleByErp($customer)
    {
        $id = false;
        $erpAccountId = $this->getEccErpaccountId($customer);
        $customerId = $customer->getId();

        $erpAcct = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
        $erpaccount_ecc_access_rights = $erpAcct->getData('erp_access_rights');
        if ($erpAccountId && $erpaccount_ecc_access_rights == 1) {
            $id = $this->rolesRoleModel->getRoleAppliedByErp($customer, $erpAccountId);
        }
        if (!$id && $erpaccount_ecc_access_rights == 2) {
            $type = strtolower($erpAcct->getAccountType());
            if ($customer->getEccErpAccountType() == "supplier") {
                $type = 'supplier';
            }
            $id = $this->getGlobalRole($type, $customer);
        }
        if (!$id && !$erpAccountId && $customer) {
            $id = $this->getGlobalRole('b2c', $customer);
        }
        return $id;
    }

    public function getGlobalRole($type, $customer)
    {
        $id = false;
        if ($this->_scopeConfig->getValue('epicor_access_control/access_role_settings/' . $type . '_default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $ids = $this->_scopeConfig->getValue('epicor_access_control/access_role_settings/' . $type . '_access_role',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $ids = explode(',', $ids);
            if ($customer && !empty($ids)) {
                $erpAccountId = $this->getEccErpaccountId($customer);
                $customerId = $customer->getId();
                $id = $this->rolesRoleModel->getRoleAppliedByErp($customer, $erpAccountId, $ids);
            } else {
                $id = $this->rolesRoleModel->getRoleFromRoles($ids);
            }
        }
        return $id;
    }

    public function getDefaultGlobalRole()
    {
        $id = false;
        if ($this->_scopeConfig->getValue('epicor_access_control/access_role_settings/b2c_default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $ids = $this->_scopeConfig->getValue('epicor_access_control/access_role_settings/b2c_access_role',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $ids = explode(',', $ids);
            $ids = $this->rolesRoleModel->getRoleAppliedByDefaultGlobal($ids);
            if (!empty($ids)) {
                $id = $this->rolesRoleModel->getRoleFromRoles($ids);
            }
        }
        return $id;
    }

    public function frontendApplyRole($customer = false)
    {
        if (!$customer) {
            $customer = $this->customerSession->getCustomer();
        }
        $allowedResources = false;
        if ($customer) {
            $this->customerSession->unsAllowedResource();
            if (!$this->isAccessRigtsEnabled()) {
                $this->customerSession->setAllowedResource($allowedResources);
                return $this;
            }
            $customer_ecc_access_rights = $customer->getData('ecc_access_rights');
            $id = false;
            if ($customer_ecc_access_rights == 1) {
                $id = $this->getRoleByCustomer($customer);
            }
            if ($customer_ecc_access_rights == 2 && !$id) {
                $id = $this->getRoleByErp($customer);
            }
            if ($id) {
                $allowedResources = $this->rolesRoleModel->getAllowedResourcesByRole($id);
                $this->customerSession->setNoRuleApplied(false);
            } else {
                $this->customerSession->setNoRuleApplied(true);
            }
            $this->customerSession->setAplliedRoleId($id);
        }
        $this->customerSession->setAllowedResource($allowedResources);


    }


}
