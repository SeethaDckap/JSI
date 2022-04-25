<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Controller\Manage;

class Customers extends \Epicor\Lists\Controller\Lists
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->loadEntity();
        $this->_view->loadLayout();
        $block = $this->_view->getLayout()->getBlock('group_customers');
        $block->setSelected($this->getRequest()->getPost('customers', null));
        $this->_view->renderLayout();
    }
}
