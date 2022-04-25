<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Customer;

class Lists extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Customer
{

    public function __construct(

    ) {
    }
    public function execute()
    {

        $this->_initCustomer();
        $this->loadLayout();
        $this->getLayout()->getBlock('customer_lists_grid')
            ->setSelected($this->getRequest()->getPost('lists', null));
        $this->renderLayout();
    }

    }
