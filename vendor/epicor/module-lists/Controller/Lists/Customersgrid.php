<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Customersgrid extends \Epicor\Lists\Controller\Lists
{
    public function execute()
    {
        $this->loadEntity();
        $this->_view->loadLayout();
        $customers = $this->getRequest()->getParam('customers');
        $this->_view->getLayout()->getBlock('list_customers')->setSelected($customers);
        $this->_view->renderLayout();
    }

}
