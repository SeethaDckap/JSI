<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Admin;

class Excludedelements extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Admin
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $this->loadLayout();
        $elements = $this->getRequest()->getParam('elements');

        $this->getLayout()->getBlock('elements.grid')->setSelected($elements);
        $this->renderLayout();
    }

    }
