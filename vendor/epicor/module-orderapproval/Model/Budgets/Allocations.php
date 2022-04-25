<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use Epicor\Comm\Helper\DataFactory as CommHelperFactory;
use Epicor\OrderApproval\Model\Budget;
use Epicor\OrderApproval\Model\BudgetManagement;
use Epicor\OrderApproval\Model\Budgets\BudgetTypes;
use Epicor\OrderApproval\Model\Budgets\Orders as BudgetOrders;
use Epicor\OrderApproval\Model\Budgets\Spend as BudgetSpend;
use Epicor\OrderApproval\Model\Budgets\Utilities as BudgetUtilities;
use Epicor\OrderApproval\Model\GroupManagement;
use Epicor\OrderApproval\Model\Groups as ApprovalGroup;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order as SalesOrder;
use Epicor\OrderApproval\Model\Groups\Budget as GroupBudget;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Collection as GroupCollection;
use Epicor\OrderApproval\Model\ErpAccountBudget;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Epicor\OrderApproval\Model\Budgets\EndDate;
use Epicor\OrderApproval\Model\BudgetRepository;
use Epicor\OrderApproval\Model\ErpAccountBudgetRepositoryFactory;
use Epicor\OrderApproval\Model\ErpAccountBudgetRepository;

class Allocations
{
    /**
     * @var BudgetSpendFactory
     */
    private $budgetSpendFactory;

    /**
     * @var CommHelperFactory
     */
    private $commHelperFactory;

    /**
     * @var Orders
     */
    private $budgetOrders;

    /**
     * @var BudgetManagement
     */
    private $budgetManagement;

    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var array
     */
    private $spendingAllocations = [];

    /**
     * @var array
     */
    private $currentBudgets = [];

    /**
     * @var bool
     */
    private $isShopperBudgetsActive = false;

    /**
     * @var bool
     */
    private $isErpBudgetsActive = false;

    /**
     * @var Utilities
     */
    private $budgetUtilities;

    /**
     * @var bool
     */
    private $isErpAccountBudgets = false;

    /**
     * @var mixed
     */
    private $erpAccountId;

    /**
     * @var \Epicor\Comm\Model\Customer
     */
    private $customer;

    /**
     * @var bool
     */
    private $isAstSent = false;

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount
     */
    private $erpAccount;

    /**
     * @var array|bool|null
     */
    private $erpSpending = [];

    /**
     * @var \Epicor\OrderApproval\Model\Budgets\BudgetTypes
     */
    private $budgetTypes;

    /**
     * @var EndDate
     */
    private $endDate;

    /**
     * Allocations constructor.
     * @param EndDate $endDate
     * @param BudgetTypes $budgetTypes
     * @param Utilities $budgetUtilities
     * @param CommHelperFactory $commHelperFactory
     * @param GroupManagement $groupManagement
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        EndDate $endDate,
        BudgetTypes $budgetTypes,
        BudgetUtilities $budgetUtilities,
        CommHelperFactory $commHelperFactory,
        GroupManagement $groupManagement,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->budgetUtilities = $budgetUtilities;
        $this->commHelperFactory = $commHelperFactory;
        $this->groupManagement = $groupManagement;
        $this->orderCollectionFactory = $orderCollectionFactory;

        $this->budgetSpendFactory = $this->budgetUtilities->getBudgetSpendFactory();
        $this->budgetOrders = $this->budgetUtilities->getBudgetOrders();
        $this->budgetManagement = $this->budgetUtilities->getBudgetManagement();
        $this->budgetTypes = $budgetTypes;
        $this->endDate = $endDate;
    }

    /**
     * return an array of budget spending, type of budget is defined by
     * method parameter i.e. is erp budgets or not
     *
     * @return array
     */
    public function getBudgetAllocations($erpAccountBudgets = false)
    {
        if ($erpAccountBudgets) {
            $this->isErpAccountBudgets = $erpAccountBudgets;
        }
        $commHelper = $this->commHelperFactory->create();
        $this->erpAccountId = $commHelper->getErpAccountId();
        $erpAccountNumber = $commHelper->getErpAccountNumber($this->erpAccountId);
        $this->erpAccount = $commHelper->getErpAccountByAccountNumber($erpAccountNumber);
        $this->customer = $commHelper->getCustomer();
        if (!$this->isErpAccountBudgets) {
            $groupItems = $this->groupManagement->getGroupByCustomer($this->customer->getId(), $this->erpAccountId);
            if ($groupItems instanceof GroupCollection) {
                /** @var ApprovalGroup $group */
                $group = $groupItems->getFirstItem();
                $this->isShopperBudgetsActive = (boolean) $group->getIsBudgetActive();
                $this->currentBudgets = $this->getCurrentShopperBudgets($group);
            }
        } else {
            $this->currentBudgets = $this->getCurrentErpBudgets();
        }

        $this->updateSpendingFromErp();
        $this->spendingAllocations = [];
        /** @var GroupBudget $budget */
        foreach ($this->currentBudgets as $budget) {
            $this->setPeriodAllocations($budget);
            $this->setBudgetsExceeded();
        }

        return $this->spendingAllocations;
    }

    /**
     * @return bool
     */
    public function isBudgetsActive()
    {
        return $this->isErpAccountBudgets ? $this->isErpBudgetsActive : $this->isShopperBudgetsActive;
    }

    /**
     * @return bool
     */
    private function updateSpendingFromErp()
    {
        if (!$this->erpSpending && !$this->isAstSent) {
            $this->erpSpending = $this->getErpSpending();
        }
        if (!$this->erpSpending || !is_array($this->erpSpending)) {
            return false;
        }
        foreach ($this->erpSpending as $range => $erpData) {
            foreach ($this->currentBudgets as $ref => $budget) {
                /** @var $budget ApprovalGroup\Budget */
                if ($range === $this->getPeriodTypeIndex($budget) && $budget->getIsErpInclude()) {
                    $this->currentBudgets[$ref]->setErpTotal($erpData->getGrandTotalInc());
                }
            }
        }
    }

    /**
     * Send AST to get the totals from ERP where any budgets set
     * to include ERP totals
     *
     * @return array
     */
    private function getErpSpending()
    {
        if ($this->isBudgetsIncludeErpSpending() && !$this->erpSpending) {
            $erpPeriods = $this->budgetManagement->getErpPeriods($this->currentBudgets);
            $erpData = [];
            if (!$this->isAstSent) {
                $erpData = $this->budgetManagement->sendAst($erpPeriods);
            }

            $this->isAstSent = true;
            return $erpData;
        }
        return [];
    }

    /**
     * Get the budget period in a format that can be compared
     * to the period index returned during the AST call
     *
     * @param ApprovalGroup\Budget | Budget $budget
     * @return string
     */
    private function getPeriodTypeIndex($budget)
    {
        $periodDateRages = '';

        if ($budget["is_erp_include"]) {
            $betweenDates = $this->budgetManagement->isBetweenDate(
                $budget['start_date'],
                $budget['duration'],
                $budget['type']
            );
            $start = $betweenDates['start_date'] ?? '';
            $end = $betweenDates['end_date'] ?? '';

            if ($start && $end) {
                $periodDateRages = $start . '_' . $end;
            }
        }

        return $periodDateRages;
    }

    /**
     * Determines if any budgets are set to include
     * Erp totals, if true then we send AST to get totals
     *
     * @return bool
     */
    private function isBudgetsIncludeErpSpending()
    {
        foreach ($this->currentBudgets as $budget) {
            /** @var $budget ApprovalGroup\Budget */
            if ($budget->getIsErpInclude()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets all budgets to exceed this would happen in a case if
     * any higher budgets were exceeded
     *
     * @return void
     */
    private function setBudgetsExceeded()
    {
        foreach ($this->spendingAllocations as $spend) {
            /** @var Spend $spend */
            $remaining = (float) $spend->getData('budget_remaining');
            if ($remaining <= 0) {
                $spend->setBudgetsExceeded(true);
            }

        }
    }

    /**
     * @param DataObject $group
     * @return array
     */
    private function getCurrentShopperBudgets($group)
    {
        $activeBudgets = [];
        $budgets = $this->budgetManagement->getShopperBudgets($group);
        if ($group->getIsBudgetActive()) {
            $activeBudgets = $this->getActiveBudgetsToday($budgets);
        }

        return $activeBudgets;
    }

    /**
     * @return array
     */
    private function getCurrentErpBudgets()
    {
        $activeBudgets = [];
        $budgetRepository = $this->budgetUtilities->getErpAccountBudgetRepositoryFactory()->create();

        $budgets = $budgetRepository->getByErpId($this->erpAccountId);
        $this->isErpBudgetsActive = (boolean) $this->erpAccount->getData('is_budget_active');

        if ($this->isErpBudgetsActive) {
            $activeBudgets = $this->getActiveBudgetsToday($budgets);
        }

        return $activeBudgets;
    }

    /**
     * @param array $budgets
     * @return array
     */
    private function getActiveBudgetsToday($budgets)
    {
        $activeBudgetToday = [];
        foreach ($budgets as $budget) {
            if ($this->budgetOrders->isBudgetValidToday($budget)) {
                $budget->setIsActiveToday(true);
            } else {
                $budget->setIsActiveToday(false);
            }
            if (!EndDate::isExpired($this->endDate->getEndDate($budget))) {
                $activeBudgetToday[ucfirst($budget->getType())] = $budget;
            }
        }
        //Get budgets in reverse order
        krsort($activeBudgetToday);

        return $activeBudgetToday;
    }

    /**
     * @param ErpAccountBudget|GroupBudget $budget
     * @throws \Exception
     */
    private function setPeriodAllocations($budget)
    {
        /** @var BudgetSpend $spending */
        $spending = $this->budgetSpendFactory->create(['data' => [
            'budget' => $budget,
            'is_erp_budget' => $this->isErpAccountBudgets
        ]]);

        $this->includeErpSpend($spending, $budget);

        if ($budget->getIsActiveToday() || EndDate::isExpired($this->endDate->getEndDate($budget))) {
            $orders = $this->getBudgetOrders($budget);
            foreach ($orders as $order) {
                $this->setOrderOnSpend($order, $spending);
                $this->updateSpending($spending, $order);
            }
        }

        $this->spendingAllocations[$spending->getData('budget_type')] = $spending;
    }

    /**
     * Includes the ERP total in the spending when available
     *
     * @param Spend $spending
     * @param ErpAccountBudget|GroupBudget $budget
     */
    private function includeErpSpend($spending, $budget)
    {
        if ($erpTotal = $budget->getErpTotal()) {
            $spent = $spending->getData('budget_spent');
            $spending->setData('budget_spent', $spent + $erpTotal);
            $remaining = $spending->getData('budget_remaining');
            $spending->setData('budget_remaining', $remaining - $erpTotal);
        }
    }

    /**
     * @param ErpAccountBudget|GroupBudget $budget
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getBudgetOrders($budget)
    {
        $customerIds = $this->getAccountCustomers();
        if (BudgetTypes::isDaily($budget)) {
            return $this->budgetOrders->getCustomerOrdersToday($customerIds, $budget->getIsErpInclude());
        } else {
            return $this->budgetOrders->getBudgetOrders($customerIds, $budget, $budget->getIsErpInclude());
        }
    }

    /**
     * @return array|mixed
     */
    private function getAccountCustomers()
    {
        if ($this->isErpAccountBudgets) {
            $budgetRepository = $this->budgetUtilities->getBudgetRepositoryFactory()->create();
            return $budgetRepository->getCustomerIdsByErpId($this->erpAccountId);
        } else {
            return $this->customer->getId();
        }
    }

    /**
     * @param SalesOrder $order
     * @param Spend $spending
     */
    private function setOrderOnSpend($order, $spending)
    {
        $orders = $spending->getData('orders');
        $orders[$order->getEntityId()] = $order;
        $spending->setData('orders', $orders);
    }

    /**
     * @param Spend $spending
     * @param SalesOrder $order
     */
    private function updateSpending($spending, $order)
    {
        $used = $spending->getData('budget_spent');
        $used += $order->getGrandTotal();
        $spending->setData('budget_spent', $used);

        $remaining = $spending->getData('budget_remaining');
        $remaining -= $order->getGrandTotal();
        $spending->setData('budget_remaining', $remaining);
    }
}
