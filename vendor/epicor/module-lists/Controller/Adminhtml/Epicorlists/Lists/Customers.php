<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Customers extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(
      \Epicor\Lists\Controller\Adminhtml\Context $context,
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
        $this->loadEntity();
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('customers_grid')->setSelected($this->getRequest()->getPost('customers', null));
        $this->_view->renderLayout();
    }

}
