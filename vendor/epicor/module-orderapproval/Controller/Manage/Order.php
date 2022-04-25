<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Manage;


class Order extends \Epicor\Lists\Controller\Lists
{
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->loadEntity();
        $layout = $this->_view->loadLayout();
        $orderBlock = $layout->getLayout()->getBlock('approval_order');
        $orderBlock->setOrderId($this->getRequest()->getParam('id'));

        $this->_view->renderLayout();
    }
}