<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use Epicor\OrderApproval\Model\Groups\Budget as GroupBudget;
use Epicor\OrderApproval\Logger\Logger;
use Epicor\OrderApproval\Model\Budgets\EndDate;
use Epicor\OrderApproval\Model\ErpAccountBudget;

class Spend extends \Magento\Framework\DataObject
{
    /** @var GroupBudget */
    private $budget;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EndDate
     */
    private $endDate;

    /**
     * @var bool
     */
    private $budgetsExceeded = false;

    /**
     * Spend constructor.
     * @param \Epicor\OrderApproval\Model\Budgets\EndDate $endDate
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        EndDate $endDate,
        Logger $logger,
        array $data = []
    ) {
        parent::__construct($data);
        $this->initialise();
        $this->logger = $logger;
        $this->endDate = $endDate;
    }

    /**
     * @return void
     */
    private function initialise()
    {
        $this->budget = $this->getData('budget');
        try {
            if ($this->isValidBudgetType()) {
                $budgetData = [
                    'budget_type' => $this->budget->getType(),
                    'budget_amount' => $this->budget->getAmount(),
                    'budget_spent' => 0,
                    'budget_remaining' => $this->budget->getAmount(),
                    'orders' => []
                ];
                $this->setData($budgetData);
            } else {
                throw new \InvalidArgumentException('Error during initialisation of Spend class, budget type invalid');
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    private function isValidBudgetType()
    {
        return $this->budget instanceof GroupBudget || $this->budget instanceof ErpAccountBudget;
    }

    /**
     * @return GroupBudget
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * @param bool $status
     */
    public function setBudgetsExceeded($status)
    {
         $this->budgetsExceeded = $status;
    }

    /**
     * @return bool
     */
    public function getBudgetsExceeded()
    {
        return $this->budgetsExceeded;
    }

    /**
     * @return string
     */
    public function getBudgetType()
    {
        return $this->budget->getType();
    }

    /**
     * @return string
     */
    public function getAllocated()
    {
        return $this->budget->getAmount();
    }

    /**
     * @return string
     */
    public function getUsed()
    {
        return $this->getData('budget_spent');
    }

    /**
     * @return string
     */
    public function getBalance()
    {
        return $this->getData('budget_remaining');
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->getBudget()->getStartDate();
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->getBudget()->getDuration();
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate->calculateBudgetEndDate(
            $this->getStartDate(),
            $this->getDuration(),
            $this->getBudgetType()
        );
    }

    /**
     * @return string
     */
    public function getOrdersIds()
    {
        $orders = $this->getData('orders');
        $orderIds = array_keys($orders);
        return implode(',', $orderIds);
    }

    /**
     * @return int
     */
    public function getOrderTotals()
    {
        $orders = $this->getData('orders');
        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getGrandTotal();
        }
        return $total;
    }

    /**
     * @return mixed
     */
    public function getErpTotal()
    {
        return $this->getBudget()->getErpTotal();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getStatus()
    {
        $status = '';
        if ($this->getBudgetsExceeded()) {
            $status .= ' exceeded';
        }
        if ($this->getBudget()->getIsActiveToday()) {
            $status .= ' active';
        }
        if (EndDate::isExpired($this->getEndDate())) {
            $status .= ' expired';
        }
        if (!EndDate::isStarted($this->getStartDate())) {
            $status .= ' new';
        }

        return $status;
    }
}
