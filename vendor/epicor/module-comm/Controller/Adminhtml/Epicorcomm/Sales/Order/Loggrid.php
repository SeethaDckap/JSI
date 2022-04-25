<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order;

class Loggrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Sales\Order
{

    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getLayout()->getBlock('');
        return $resultPage;
    }
       /* $this->loadLayout();
        $block = $this->getLayout()->createBlock('epicor_comm/adminhtml_sales_order_view_tab_log');
        $this->getResponse()->setBody($block->toHtml());
    }*/

}
