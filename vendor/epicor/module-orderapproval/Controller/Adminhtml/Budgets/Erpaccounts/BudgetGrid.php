<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Budgets\Erpaccounts;

use Magento\Framework\View\Result\Layout as ResultLayout;

class BudgetGrid extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $layout = $this->_resultLayoutFactory->create();
        $layout->getLayout()
            ->getBlock('budget_add_button')
            ->setData('erp_id', $this->getErpId());

        return $layout;
    }

    private function getErpId()
    {
        return $this->getRequest()->getParam('erp_id');
    }
}
