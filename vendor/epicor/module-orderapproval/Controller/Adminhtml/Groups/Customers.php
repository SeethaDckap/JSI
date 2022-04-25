<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\OrderApproval\Controller\Adminhtml\Groups;

class Customers extends Groups
{
    /**
     * Customers initial grid tab load
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('customers_grid')
            ->setSelected($this->getRequest()->getPost('customers',
                null));
        $this->_view->renderLayout();
    }

}
