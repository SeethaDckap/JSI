<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Right;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Right
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $this->_loadRight($id);
        $this->loadLayout();
        $this->renderLayout();
    }

    }
