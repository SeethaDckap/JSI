<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab;

class AddBudget extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl('orderapproval/budgets_erpaccounts/create', ['erp_id' => $this->getErpId()]);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('orderapproval/budgets_erpaccounts/grid', ['erp_id' => $this->getErpId()]);
    }

    /**
     * @return mixed
     */
    private function getErpId()
    {
        return  $this->getRequest()->getParam('erp_id');
    }

    /**
     * @return string
     */
    public function getEndDateApiUrl()
    {
        return $this->getUrl('/rest/V1/budgets/get-end-date');
    }
}