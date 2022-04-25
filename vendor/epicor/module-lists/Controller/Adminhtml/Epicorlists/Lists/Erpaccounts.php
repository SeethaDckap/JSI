<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Erpaccounts extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{
     /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
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
        $this->loadEntity();
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('erpaccounts_grid')->setSelected($this->getRequest()->getPost('erpaccounts', null));
        $this->_view->renderLayout();
    }
}
