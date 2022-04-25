<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Edit extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Role edit action
     *
     * @return void
     */
    public function execute()
    {
        $role = $this->loadEntity();
        $resultPage = $this->_resultPageFactory->create();
        
        $title = __('New Role');
        if ($role->getId()) {
            if(!$this->registry->registry('IsDuplicateRole')) {
                $title = $role->getTitle();
                $title = __('Role: %1', $title);
            }
        }
        
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }

}
