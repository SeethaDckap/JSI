<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Controller\Manage;

class ParentGrid extends \Epicor\Lists\Controller\Lists
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->loadEntity();
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
