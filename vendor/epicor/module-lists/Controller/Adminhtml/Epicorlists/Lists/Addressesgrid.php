<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

class Addressesgrid extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{
    public function __construct(
     \Epicor\Lists\Controller\Adminhtml\Context $context,
     \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct($context, $backendAuthSession);
    }
    /**
     * Brands ajax reload of grid tab
     *
     * @return void
     */
    public function execute()
    {
        $this->loadEntity();        
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('addresses_grid')->setSelected($this->getRequest()->getPost('addresses', null));
        $this->_view->renderLayout();
    }

}
