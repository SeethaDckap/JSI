<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Delete extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Deletes single Role
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $this->delete($id);
        $this->_redirect('*/*/');
    }

}
