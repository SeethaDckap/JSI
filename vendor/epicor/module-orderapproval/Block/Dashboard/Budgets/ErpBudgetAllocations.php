<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Dashboard\Budgets;

class ErpBudgetAllocations extends \Epicor\OrderApproval\Block\Dashboard\Budgets\ShopperBudgetAllocations
{
    const FRONTEND_RESOURCE_BUDGET_VIEW = 'Epicor_Customerconnect::customerconnect_budgets_read';

    /**
     * @var bool
     */
    protected $isErpBudgets = true;

    /**
     * Access Rights read protection.
     *
     * @return bool
     */
    public function isBudgetReadAllowed()
    {
        if (!$this->_isAccessAllowed(self::FRONTEND_RESOURCE_BUDGET_VIEW) || $this->isMasquerading()) {
            return false;
        }
        return true;
    }
}
