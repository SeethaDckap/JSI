<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Products extends \Epicor\Lists\Controller\Lists
{

    public function execute()
    {
        $this->loadEntity();
        $products = $this->getRequest()->getParam('list_id');
        $this->_view->loadLayout();
        $this->_view->getLayout()->getBlock('list_products')->setSelected($products);
        $this->_view->renderLayout();
    }

}
