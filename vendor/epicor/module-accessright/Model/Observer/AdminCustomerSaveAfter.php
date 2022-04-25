<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model\Observer;

use Epicor\Common\Observer\AbstractObserver;

class AdminCustomerSaveAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    /**
     * 
     * @param \Epicor\Comm\Helper\Context $context
     * @param \Epicor\AccessRight\Model\RoleModel\CustomerFactory $accessroleCustomerFactory
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\AccessRight\Model\RoleModel\CustomerFactory $accessroleCustomerFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->customerRepository = $context->getCustomerRepository();
        $this->customerCustomerFactory = $context->getCustomerFactory();
        $this->accessroleCustomerFactory = $accessroleCustomerFactory;
        $this->request = $request;
    }

    /**
     * Does any custom saving of a customer after save action in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $request = $event->getRequest();
        $data = $request->getPost();
        $customerRepository = $this->customerRepository->getById($customer->getId());
        $customerRepository->setCustomAttribute('ecc_access_rights', $data['customer_access_rights']);
        $accessRoles = isset($data['customer_access_roles']) ? $data['customer_access_roles'] : [];
        if ($data['customer_access_rights'] == 1) {
            $accessRoleOptions = ($accessRoles) ? implode(',', $data['customer_access_roles']) : $accessRoles;
            $customerRepository->setCustomAttribute('ecc_access_roles', $accessRoleOptions);
        }

        $this->customerRepository->save($customerRepository);

        $customerAccessRoleModel = $this->accessroleCustomerFactory->create();
        $customerAccessRoleModelCollection = $customerAccessRoleModel->getCollection()
                ->addFieldToFilter('customer_id', $customer->getId())
                ->addFieldToFilter('by_customer', 1);
        if ($customerAccessRoleModelCollection->getData()) {
            foreach ($customerAccessRoleModelCollection as $val) {
                if ($val['by_role'] == 0 && $val['by_customer'] == 1) {
                    $val->delete();
                }
                if ($val['by_role'] == 1 && $val['by_customer'] == 1) {
                    $customerAccessRoleModel->load($val['id']);
                    $model = $customerAccessRoleModel->load($val['id'])->setByCustomer(0);
                    $model->save();
                }
                
            }
        }
        if ($accessRoles) {
            foreach ($accessRoles as $val) {
                $customerAccessRoleModelCollection = $customerAccessRoleModel->getCollection()
                        ->addFieldToFilter('customer_id', $customer->getId())
                        ->addFieldToFilter('access_role_id', $val);
                if ($customerAccessRoleModelCollection->getData()) {
                    foreach ($customerAccessRoleModelCollection as $roles) {
                        if ($roles['access_role_id'] == $val && $roles['by_role'] == 1) {
                            $customerAccessRoleModel = $this->accessroleCustomerFactory->create();
                            $customerAccessRoleModel->load($roles['id']);
                            $customerAccessRoleModel->setByCustomer(1);
                            $customerAccessRoleModel->save();
                        }
                    }
                } else {
                    $customerAccessRoleModel = $this->accessroleCustomerFactory->create();
                    $customerAccessRoleModel->setAccessRoleId($val);
                    $customerAccessRoleModel->setCustomerId($customer->getId());
                    $customerAccessRoleModel->setByCustomer(1);
                    $customerAccessRoleModel->setByRole(0);
                    $customerAccessRoleModel->save();
                }
            }
        }
    }
}
