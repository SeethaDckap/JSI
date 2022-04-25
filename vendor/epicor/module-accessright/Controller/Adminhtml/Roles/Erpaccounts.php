<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Erpaccounts extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
     /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * ERP Accounts initial grid tab load
     *
     * @return void
     */
    public function execute()
    {
        $role = $this->loadEntity();
        $role->getErpAccountModel()->getConditions()->setJsFormObject('rule_erp_conditions_fieldset'); //condition
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('erpaccounts_grid')->setSelected($this->getRequest()->getPost('erpaccounts', null));
        $this->_view->renderLayout();
    }
}
