<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use Epicor\OrderApproval\Model\Budget;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Epicor\OrderApproval\Model\BudgetManagement;
use Epicor\OrderApproval\Model\Budgets\BudgetTypes;
use Epicor\OrderApproval\Model\Budgets\Orders as BudgetOrders;
use Epicor\OrderApproval\Model\Budgets\Spend as BudgetSpend;
use Epicor\OrderApproval\Model\Budgets\SpendFactory as BudgetSpendFactory;
use Epicor\OrderApproval\Model\BudgetRepositoryFactory;
use Epicor\OrderApproval\Model\ErpAccountBudgetRepositoryFactory;
use Epicor\OrderApproval\Model\Budgets\PeriodFactory;
use Epicor\OrderApproval\Model\Groups\Budget as OrderBudget;
use Epicor\OrderApproval\Model\Budgets\Period as BudgetPeriod;

class Utilities
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var BudgetManagement
     */
    private $budgetManagement;

    /**
     * @var Orders
     */
    private $budgetOrders;

    /**
     * @var SpendFactory
     */
    private $budgetSpendFactory;

    /**
     * @var BudgetRepositoryFactory
     */
    private $budgetRepositoryFactory;

    /**
     * @var ErpAccountBudgetRepositoryFactory
     */
    private $erpAccountBudgetRepositoryFactory;

    /**
     * Utilities constructor.
     * @param ErpAccountBudgetRepositoryFactory $erpAccountBudgetRepositoryFactory
     * @param BudgetRepositoryFactory $budgetRepositoryFactory
     * @param BudgetManagement $budgetManagement
     * @param Orders $budgetOrders
     * @param SpendFactory $budgetSpendFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        ErpAccountBudgetRepositoryFactory $erpAccountBudgetRepositoryFactory,
        BudgetRepositoryFactory $budgetRepositoryFactory,
        BudgetManagement $budgetManagement,
        BudgetOrders $budgetOrders,
        BudgetSpendFactory $budgetSpendFactory,
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
        $this->budgetManagement = $budgetManagement;
        $this->budgetOrders = $budgetOrders;
        $this->budgetSpendFactory = $budgetSpendFactory;
        $this->budgetRepositoryFactory = $budgetRepositoryFactory;
        $this->erpAccountBudgetRepositoryFactory = $erpAccountBudgetRepositoryFactory;
    }

    /**
     * @param $inputDate
     * @return string|string[]
     * @throws LocalizedException
     */
    public function getUtcDate($inputDate)
    {
        $date = str_replace('-', '/', $inputDate);
        if ($date) {
            $date = $this->timezone->convertConfigTimeToUtc($date);
        }

        return $date;
    }

    /**
     * @param $value
     * @return string
     */
    public static function getAmountFourPlaceDecimal($value)
    {
        if (is_numeric($value)) {
            return number_format($value, 4);
        }

        return 0;
    }

    /**
     * @return BudgetManagement
     */
    public function getBudgetManagement()
    {
        return $this->budgetManagement;
    }

    /**
     * @return SpendFactory
     */
    public function getBudgetSpendFactory()
    {
        return $this->budgetSpendFactory;
    }

    /**
     * @return Orders
     */
    public function getBudgetOrders()
    {
        return $this->budgetOrders;
    }

    /**
     * @return BudgetRepositoryFactory
     */
    public function getBudgetRepositoryFactory()
    {
        return $this->budgetRepositoryFactory;
    }

    /**
     * @return ErpAccountBudgetRepositoryFactory
     */
    public function getErpAccountBudgetRepositoryFactory()
    {
        return $this->erpAccountBudgetRepositoryFactory;
    }
}