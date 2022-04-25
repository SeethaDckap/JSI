<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Controller\Manage;

class Budgets extends \Epicor\Lists\Controller\Lists
{
    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    private $layout;

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->loadEntity();
        $this->layout = $this->_view->loadLayout();
        $this->layout->getLayout()->getBlock('budget_tab');

        $this->_view->renderLayout();
    }
}
