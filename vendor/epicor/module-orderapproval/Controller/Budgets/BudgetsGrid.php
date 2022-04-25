<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Budgets;


use Magento\Framework\App\ResponseInterface;

class BudgetsGrid extends \Epicor\AccessRight\Controller\Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}