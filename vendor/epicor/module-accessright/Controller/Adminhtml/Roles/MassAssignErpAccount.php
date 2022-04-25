<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class MassAssignErpAccount extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Epicor\AccessRight\Helper\Admin
     */
    protected $rolesAdminHelper;

    /*
     * @var \Epicor\AccessRight\Model\RoleModelFactory
     */
    protected $rolesRoleModelFactory;

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\AccessRight\Helper\Admin $rolesAdminHelper,
        \Epicor\AccessRight\Model\RoleModelFactory $rolesRoleModelFactory
    ) {        
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->rolesAdminHelper = $rolesAdminHelper;
        $this->rolesRoleModelFactory = $rolesRoleModelFactory;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Assign ERP Account Role
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('roleid');
        $erpAccountId = $this->getRequest()->getParam('assign_erp_account');
        
        $erpAccount = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */

        if ($erpAccount->isObjectNew()) {
            $this->messageManager->addError(__('Please select an Erp Account.'));
        } else {

            $returnValues = $this->rolesAdminHelper->assignErpAccountRolesCheck($ids, $erpAccount);

            if ($returnValues) {
                if (!empty($returnValues['success']['id'])) {
                    $allowedRoles = $returnValues['success']['values'];
                    foreach ($allowedRoles as $roleId){
                        $role = $this->rolesRoleModelFactory->create()->load($roleId);
                        $role->addErpAccounts($erpAccountId);
                        $role->save();
                    }

                    $this->messageManager->addSuccess(__('ERP Account assigned to ' . count(array_keys($returnValues['success']['values'])) . ' Roles. ' . "Role Id: (" . $returnValues['success']['id'] . ")"));
                }
                if (!empty($returnValues['error']['id'])) {
                    $this->messageManager->addError(__('ERP Account not assigned to ' . count(array_keys($returnValues['error']['values'])) . ' Roles. ' . "Role Id: (" . $returnValues['error']['id'] . ")"));
                }
            }
        }

        $this->_redirect('*/*/');
    }

}
