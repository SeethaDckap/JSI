<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Right;

class NewAction extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Right
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $this->_forward('edit');
    }

    }
