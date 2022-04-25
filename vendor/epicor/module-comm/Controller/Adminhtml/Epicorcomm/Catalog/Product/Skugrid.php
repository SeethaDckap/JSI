<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Catalog\Product;

class Skugrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Catalog\Product
{

    public function __construct(

    ) {
    }
    public function execute()
    {
        $this->_initProduct();
        $this->loadLayout(false)->renderLayout();
    }

}
