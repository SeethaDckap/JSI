<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Plugin\Budgets\Erp;

use Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tabs as ErpAccountTab;

class ErpAccountsTabPlugin
{
    /**
     * @var string[]
     */
    private $excludedErpAccountTypes = ['B2C', 'Dealer', 'Supplier'];

    /**
     * @param ErpAccountTab $subject
     * @param string|ErpAccountTab $result
     * @return ErpAccountTab
     * @throws \Exception
     */
    public function afterGetAdditionalTabs(ErpAccountTab $subject, $result)
    {
        $customer = $subject->getCustomerAccount();
        $accountType = $customer->getData('account_type');
        $erpId = $this->getErpId($subject);
        if ($erpId && !$this->isExcludedErpAccountType($accountType)) {
            $result = $this->getBudgetTab($subject, $erpId);
        }

        return $result;
    }

    /**
     * @param $accountType
     * @return bool
     */
    private function isExcludedErpAccountType($accountType)
    {
        $typesExcluded = array_map('strtolower', $this->excludedErpAccountTypes);

        return in_array(strtolower($accountType), $typesExcluded);
    }

    /**
     * @param ErpAccountTab $subject
     * @return mixed
     */
    private function getErpId($subject)
    {
        if ($id = $subject->getRequest()->getParam('id')) {
            return $id;
        }
    }

    /**
     * @param ErpAccountTab $subject
     * @param  string $erpId
     * @return mixed
     */
    private function getBudgetTab($subject, $erpId)
    {
        return $subject->addTab('budget_information', array(
            'label' => 'Budget Information',
            'title' => 'Budget Information',
            'id' => 'erp-budget-information',
            'url' => $subject->getUrl('orderapproval/budgets_erpaccounts/budgetgrid', ['erp_id' => $erpId]),
            'class' => 'ajax budget-information-tab'
        ));
    }
}
