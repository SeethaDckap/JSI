<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group;

class Index extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $this->_initPage()
            ->renderLayout();
    }

    }
