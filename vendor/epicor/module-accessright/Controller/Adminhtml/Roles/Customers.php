<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Customers extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(
        \Epicor\AccessRight\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendSession
    ) {
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
        parent::__construct($context, $backendSession);
    }

    /**
     * Customers initial grid tab load
     *
     * @return void
     */
    public function execute()
    {
        $role = $this->loadEntity();
        $role->getCustomerModel()->getConditions()->setJsFormObject('rule_customer_fieldset'); //condition
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('customers_grid')->setSelected($this->getRequest()->getPost('customers',
            null));
        $this->_view->renderLayout();
    }

}
