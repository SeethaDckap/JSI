<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use DateTime;
use Epicor\OrderApproval\Model\Budgets\BudgetIntervalFactory;
use Epicor\OrderApproval\Model\Budgets\BudgetTypes;
use InvalidArgumentException;
use Epicor\OrderApproval\Logger\Logger;

/**
 * Defines the total period of a budget, builds the intervals for the period
 *
 * Class Period
 * @package Epicor\OrderApproval\Model\Budgets
 */
class Period extends \Magento\Framework\DataObject
{
    /**
     * @var string
     */
    private $budgetStart;

    /**
     * @var string
     */
    private $duration;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $budgetIntervals = [];

    /**
     * @var BudgetIntervalFactory
     */
    private $budgetIntervalFactory;

    /**
     * @var BudgetTypes
     */
    private $budgetTypes;

    /**
     * @var int
     */
    private $day;

    /**
     * @var int
     */
    private $month;

    /**
     * @var int
     */
    private $year;

    /**
     * @var string
     */
    private $intervalSize;

    /**
     * @var int
     */
    private $iterator;

    /**
     * @var bool
     */
    private $periodStart = true;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Period constructor.
     * @param Logger $logger
     * @param BudgetIntervalFactory $budgetIntervalFactory
     * @param BudgetTypes $budgetTypes
     * @param array $data
     */
    public function __construct(
        Logger $logger,
        BudgetIntervalFactory $budgetIntervalFactory,
        BudgetTypes $budgetTypes,
        array $data = []
    ) {
        parent::__construct($data);

        $this->budgetIntervalFactory = $budgetIntervalFactory;
        $this->budgetTypes = $budgetTypes;
        $this->logger = $logger;
        $this->budgetStart = $this->getData('budgetStart');
        $this->duration = $this->getData('duration');
        $this->type = $this->getData('type');

        $this->setBudgetIntervals();
    }

    /**
     * @return BudgetInterval
     */
    public function getCurrentPeriodInterval()
    {
        foreach ($this->budgetIntervals as $interval) {
            /** @var BudgetInterval $interval */
            if ($this->isCurrentInterval($interval)) {
                return $interval;
            }
        }
    }

    /**
     * @param BudgetInterval $interval
     * @return bool
     */
    private function isCurrentInterval(BudgetInterval $interval)
    {
        $now = new DateTime();
        return ($interval->getIntervalStart() < $now && $interval->getIntervalEnd() > $now)
            || ($interval->getIntervalStart() == $now)
            || ($interval->getIntervalStart() == $now);
    }


    /**
     * @return void
     */
    private function setBudgetIntervals()
    {
        try {
            if (!$this->budgetStart || !$this->duration || !$this->type) {
                throw new InvalidArgumentException('Error: Period params not set needs start date, duration and type');
            }
            if (!$this->isValidBudgetParams()) {
                throw new InvalidArgumentException('Error: Period params passed are not of valid type');
            }
            $this->setPeriodIteration();
            $nextIntervalStart = null;
            for ($i = $this->iterator; $i < $this->iterator + $this->duration; $i++) {
                $nextIntervalStart = $this->buildInterval($nextIntervalStart);
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    private function isValidBudgetParams()
    {
        return BudgetTypes::isValidStartDate($this->budgetStart)
            && BudgetTypes::isValidDuration($this->duration)
            && BudgetTypes::isValidBudgetType($this->type);
    }

    /**
     * @return void
     */
    private function setPeriodIteration()
    {
        $this->initialisePeriodStart();
        switch (strtolower($this->type)) {
            case strtolower(BudgetTypes::BUDGET_TYPE_MONTHLY):
                $this->intervalSize = '+1 month';
                $this->iterator = $this->month;
                break;
            case strtolower(BudgetTypes::BUDGET_TYPE_YEARLY):
                $this->intervalSize = '+1 year';
                $this->iterator = $this->year;
                break;
            case strtolower(BudgetTypes::BUDGET_TYPE_QUARTERLY):
                $this->intervalSize = '+3 months';
                $this->iterator = $this->month;
                break;
            case strtolower(BudgetTypes::BUDGET_TYPE_DAILY):
                $this->intervalSize = '+1 day';
                $this->iterator = $this->day;
                break;

            default:
                $this->intervalSize = 0;
                $this->iterator = 0;
        }
    }

    /**
     * @return void
     */
    private function initialisePeriodStart()
    {
        try {
            $periodStartDate = new DateTime($this->budgetStart);
            $this->day = (int) $periodStartDate->format('d');
            $this->month = (int) $periodStartDate->format('m');
            $this->year =(int) $periodStartDate->format('Y');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param $nextIntervalStart
     * @return DateTime
     */
    private function buildInterval($nextIntervalStart)
    {
        try {
            /** @var BudgetInterval $interval */
            $interval = $this->budgetIntervalFactory->create();

            if ($this->periodStart) {
                $intervalStart = new DateTime($this->getPeriodStartDate());
                $this->periodStart = false;
            } else {
                $intervalStart = $nextIntervalStart;
            }

            $intervalEnd = clone $intervalStart;
            $intervalEnd->modify($this->intervalSize);
            $nextIntervalStart = clone $intervalEnd;
            $intervalEnd->modify('-1 second');
            $interval->setBudgetInterval($intervalStart, $intervalEnd);

            $this->budgetIntervals[] = $interval;

            return $nextIntervalStart;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @return string
     */
    private function getPeriodStartDate()
    {
        return $this->year . '-' . $this->month . '-' . $this->day;
    }
}