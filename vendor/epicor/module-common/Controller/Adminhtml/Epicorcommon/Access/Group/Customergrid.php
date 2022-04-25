<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group;

class Customergrid extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $this->_loadGroup($id);
        $this->loadLayout();
        $this->getLayout()->getBlock('customer.grid')->setSelected($this->getRequest()->getPost('customers', null));
        $this->renderLayout();
    }

    }
