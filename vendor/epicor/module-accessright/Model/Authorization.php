<?php
/**
 * Magento Authorization component. Can be used to add authorization facility to any application
 *
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model;

class Authorization
{

    protected $isAccessEnabled = null;

    /**
     * Acl resource provider
     *
     * @var \Epicor\AccessRight\Acl\AclResource\ProviderInterface
     */
    protected $_aclResourceProvider;

    protected $rolesRoleModelFactory;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Session\Epicor\AccessRight\Model\ApplyRoles
     */
    protected $applyRole;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Epicor\AccessRight\Helper\AccessRoles
     */
    protected $eccAccessRoles;

    /**
     * @param \Magento\Framework\Authorization\PolicyInterface $aclPolicy
     * @param \Magento\Framework\Authorization\RoleLocatorInterface $roleLocator
     * @param \Epicor\AccessRight\Helper\AccessRoles $eccAccessRoles
     */
    public function __construct(
        \Epicor\AccessRight\Acl\AclResource\ProviderInterface $aclResourceProvider,
        \Epicor\AccessRight\Model\RoleModelFactory $rolesRoleModelFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\AccessRight\Model\ApplyRoles $applyRole,
        \Magento\Framework\App\State $state,
        \Epicor\AccessRight\Helper\AccessRoles $eccAccessRoles
    )
    {

        $this->_aclResourceProvider = $aclResourceProvider;
        $this->rolesRoleModelFactory = $rolesRoleModelFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->applyRole = $applyRole;
        $this->_state = $state;
        $this->eccAccessRoles = $eccAccessRoles;
    }


    /**
     * Get AccessRoles Object
     *
     * @return \Epicor\AccessRight\Helper\AccessRoles
     */
    public function getEccAccessRoles()
    {
        return $this->eccAccessRoles;
    }


    /**
     * Get scope config
     *
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    public function isAccessRigtsEnabled()
    {
        if ($this->isAccessEnabled === null) {
            $this->isAccessEnabled = $this->getScopeConfig()->isSetFlag(
                'epicor_access_control/global/enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->isAccessEnabled;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @param string $resource
     * @param string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        if ($this->_state->getAreaCode() == 'adminhtml' || !$this->applyRole->isAccessRigtsEnabled()) {
            return true;
        }
        if ($this->customerSession->getNoRuleApplied()) {
            return true;
        }
//        $this->applyRole->frontendApplyRole();
//        echo $this->customerSession->getAplliedRoleId().'<br>';
        $allowed = $this->getAllowedResource();
        if (!$this->customerSession->isLoggedIn() && !$allowed) {
            $erpAccountId = $this->getScopeConfig()->getValue(
                'customer/create_account/default_erpaccount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($erpAccountId) {
                $id = $this->applyRole->getRoleByDefaultErp();
                $allowed = false;
                if ($id) {
                    $allowed = $this->rolesRoleModelFactory->create()->getAllowedResourcesByRole($id);
                }
            }
            $this->customerSession->setAplliedRoleId($id);
            $this->customerSession->setAllowedResource($allowed);
        } else {
            $allowed = $this->getAllowedResource();
        }

        if (!$this->customerSession->getAplliedRoleId()) {
            $this->customerSession->setNoRuleApplied(true);
        } else {
            $this->customerSession->setNoRuleApplied(false);
        }
        if ($allowed !== false && !in_array($resource, $allowed)) {
            return false;
        }
        return true;
    }

    public function getMessage()
    {
        return __('You do not have access permissions to view this page.');
    }

    public function getAllResuource()
    {
        return $this->_aclResourceProvider->getAclResources();
    }


    public function getAllowedResource()
    {
        return $this->customerSession->getAllowedResource();
    }


}
