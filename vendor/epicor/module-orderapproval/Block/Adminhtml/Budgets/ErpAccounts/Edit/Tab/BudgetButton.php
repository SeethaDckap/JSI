<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab;

use Epicor\OrderApproval\Model\Config\Budgets\Source\BudgetTypes;
use Magento\Backend\Block\Template\Context;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;

class BudgetButton extends \Magento\Backend\Block\Template
{
    /**
     * @var BudgetTypes
     */
    private $budgetTypes;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * BudgetButton constructor.
     * @param GroupCustomers $groupCustomers
     * @param BudgetTypes $budgetTypes
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        GroupCustomers $groupCustomers,
        BudgetTypes $budgetTypes,
        Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->budgetTypes = $budgetTypes;
        $this->groupCustomers = $groupCustomers;
    }

    /**
     * @return int
     */
    public function getBudgetsRemaining()
    {
        if ($erpId = $this->getErpId()) {
            $remainingTypes = $this->budgetTypes->getRemainingErpOptions($erpId);
            return count($remainingTypes);
        }
    }

    /**
     * @return int
     */
    public function getGroupBudgetsRemaining()
    {
        if ($groupId = $this->getGroupId()) {
            $remainingTypes = $this->budgetTypes->getRemainingShopperOptions($groupId);
            return count($remainingTypes);
        }

        return 0;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDisableBudget()
    {
        if ($this->getGroupId() && $this->groupCustomers->isEditableByCustomer()) {
            return '';
        } else {
            return 'disabled="disabled"';
        }
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return parent::getCacheKey() . $this->getBudgetsRemaining();
    }

    /**
     * @return string
     */
    public function getAddButtonDisabled()
    {
        if ($this->getBudgetsRemaining() > 0) {
            return '';
        } else {
            return 'disabled="disabled"';
        }
    }

    /**
     * @return bool
     */
    public function isBudgetActive()
    {
        return $this->getGroupId() ? true : false;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return mixed
     */
    public function getErpId()
    {
        return $this->getRequest()->getParam('erp_id');
    }
}