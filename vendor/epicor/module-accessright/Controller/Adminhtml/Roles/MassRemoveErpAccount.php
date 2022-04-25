<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class MassRemoveErpAccount extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;
    /*
     * @var \Epicor\AccessRight\Model\RoleModelFactory
     */
    protected $rolesRoleModelFactory;

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Epicor\AccessRight\Model\RoleModelFactory $rolesRoleModelFactory
    ) {
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        $this->rolesRoleModelFactory = $rolesRoleModelFactory;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Remove ERP Account Roles
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('roleid');
        $erpAccountId = $this->getRequest()->getParam('remove_erp_account');

        $erpAccount = $this->commCustomerErpaccountFactory->create()->load($erpAccountId);
        /* @var $erpAccount Epicor\Comm\Model\Customer\Erpaccount */

        if ($erpAccount->isObjectNew()) {
            $this->messageManager->addError(__('Please select an Erp Account.'));
        } else {
            foreach ($ids as $roleId){
                $role = $this->rolesRoleModelFactory->create()->load($roleId);
                $role->removeErpAccounts($erpAccountId);
                $role->save();
            }
            $this->messageManager->addSuccess(__('ERP Account removed from ' . count($ids) . ' Roles '));
        }

        $this->_redirect('*/*/');
    }

}
