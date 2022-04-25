<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class NewAction extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * new Role action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }

}
