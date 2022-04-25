<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Quickadd;

class Autocomplete extends \Epicor\Comm\Controller\Quickadd
{


    public function execute()
    {
        $sku = $this->getRequest()->getParam('sku', false);
       // $this->getResponse()->setBody($this->getLayout()->createBlock('epicor_comm/cart_quickadd_autocomplete')->toHtml());
       $layout = $this->_view->getLayout();
       $block = $layout->createBlock('Epicor\Comm\Block\Cart\Quickadd\Autocomplete');
       $this->getResponse()->appendBody($block->toHtml());
         
    }
}
