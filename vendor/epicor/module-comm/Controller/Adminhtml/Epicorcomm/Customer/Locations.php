<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer;

class Locations extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getLayout()->getBlock('customer_locations_wrapper')
            ->setSelected($this->getRequest()->getPost('locations', null));
        $this->renderLayout();
    }

    }
