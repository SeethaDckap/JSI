<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order;

class Addproduct extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    }
