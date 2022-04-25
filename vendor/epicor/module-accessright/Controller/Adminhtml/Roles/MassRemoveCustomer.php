<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class MassRemoveCustomer extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;
    /*
     * @var \Epicor\AccessRight\Model\RoleModelFactory
     */
    protected $rolesRoleModelFactory;

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\AccessRight\Model\RoleModelFactory $rolesRoleModelFactory
    ) {
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->rolesRoleModelFactory = $rolesRoleModelFactory;
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Remove Customer Roles
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('roleid');
        $customerId = $this->getRequest()->getParam('remove_customer');

        $customer = $this->customerCustomerFactory->create()->load($customerId);
        /* @var $customer Epicor\Comm\Model\Customer */

        if ($customer->isObjectNew()) {
            $this->messageManager->addError(__('Please select a Customer.'));
        } else {
            foreach ($ids as $roleId){
                $role = $this->rolesRoleModelFactory->create()->load($roleId);
                $role->removeCustomers($customerId);
                $role->save();
            }
            $this->messageManager->addSuccess(__('Customer removed from ' . count($ids) . ' Roles '));
        }

        $this->_redirect('*/*/');
    }

    }
