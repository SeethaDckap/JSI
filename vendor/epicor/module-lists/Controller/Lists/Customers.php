<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Customers extends \Epicor\Lists\Controller\Lists
{
    public function execute()
    {
        $this->loadEntity();
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('list_customers')->setSelected($this->getRequest()->getPost('customers', null));
        $this->_view->renderLayout();
    }

}
