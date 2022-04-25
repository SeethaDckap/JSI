<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group;

class Rightsgrid extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $this->_loadGroup($id);
        $this->loadLayout();
        $this->getLayout()->getBlock('rights.grid')->setSelected($this->getRequest()->getPost('rights', null));
        $this->renderLayout();
    }

    }
