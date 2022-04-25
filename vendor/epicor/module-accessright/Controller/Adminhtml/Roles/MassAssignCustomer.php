<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class MassAssignCustomer extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\AccessRight\Helper\Admin
     */
    protected $rolesAdminHelper;

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\AccessRight\Helper\Admin $rolesAdminHelper
    ) {
        $this->backendSession = $context->getBackendSession();
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->rolesAdminHelper = $rolesAdminHelper;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Assign Customer Lists
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('roleid');
        $customerId = $this->getRequest()->getParam('assign_customer');
        $customer = $this->customerCustomerFactory->create()->load($customerId);
        /* @var $customer \Epicor\Comm\Model\Customer */

        if ($customer->isObjectNew()) {
            $this->messageManager->addError(__('Please select a Customer.'));
        } else {
            $returnValues = $this->rolesAdminHelper->assignCustomerAccountRolesCheck($ids, $customer);

            if ($returnValues) {
                if (!empty($returnValues['success']['values'])) {
                    $allowedRoles = $returnValues['success']['values'];
                    foreach ($allowedRoles as $roleId){
                        $role = $this->rolesRoleModelFactory->create()->load($roleId);
                        $role->addCustomers($customerId);
                        $role->save();
                    }

                    $this->messageManager->addSuccess(__('Customer Account assigned to ' . count(array_keys($returnValues['success']['values'])) . ' Roles. ' . "Role Id: (" . $returnValues['success']['id'] . ")"));
                }
                if (!empty($returnValues['error']['id'])) {
                    $this->messageManager->addError(__('Customer Account not assigned to ' . count(array_keys($returnValues['error']['values'])) . ' Roles. ' . "Role Id: (" . $returnValues['error']['id'] . ")"));
                }
            }
        }

        $this->_redirect('*/*/');
    }

}
