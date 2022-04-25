<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class MassAssignStatus extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{

    /**
     * @var \Epicor\AccessRight\Model\RoleModelFactory
     */
    protected $rolesRoleModelFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->rolesRoleModelFactory = $context->getRolesRoleModelFactory();
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Assign Status Roles
     *
     * @return void
     */
    public function execute()
    {
        $ids = (array) $this->getRequest()->getParam('roleid');
        $assign_status = $this->getRequest()->getParam('assign_status');

        $includeIds = array();

        foreach ($ids as $id) {
            $role = $this->rolesRoleModelFactory->create()->load($id);
            $includeIds[] = $id;
            $role->setActive($assign_status);
            $role->save();

        }

        $changedIds = rtrim(implode(',', $includeIds), ',');

        if (!empty($changedIds)) {
            $this->messageManager->addSuccess(__('Role Status  changed to ' . count(array_keys($includeIds)) . ' Roles. ' . "Role Id: (" . $changedIds . ")"));
        }

        $this->_redirect('*/*/');
    }

}
