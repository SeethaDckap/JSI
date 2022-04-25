<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use Epicor\OrderApproval\Model\Groups\Budget;
use Magento\Framework\Stdlib\DateTime\Timezone as DateTimeZone;
use Epicor\OrderApproval\Logger\Logger;

class BudgetTypes
{
    const BUDGET_TYPE_DAILY  = 'Daily';
    const BUDGET_TYPE_MONTHLY = 'Monthly';
    const BUDGET_TYPE_QUARTERLY = 'Quarterly';
    const BUDGET_TYPE_YEARLY = 'Yearly';

    /**
     * @var DateTimeZone
     */
    private $timezone;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * BudgetTypes constructor.
     * @param DateTimeZone $timezone
     * @param Logger $logger
     */
    public function __construct(
        DateTimeZone $timezone,
        Logger $logger
    ) {
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    /**
     * @return string[]
     */
    public static function getBudgetTypesList()
    {
        return [
            self::BUDGET_TYPE_DAILY,
            self::BUDGET_TYPE_MONTHLY,
            self::BUDGET_TYPE_QUARTERLY,
            self::BUDGET_TYPE_YEARLY
        ];
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isValidBudgetType($type)
    {
        foreach (self::getBudgetTypesList() as $budgetType) {
            if (strtolower($type) === strtolower($budgetType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $budget
     * @return bool
     */
    public static function isDaily($budget)
    {
        return strtolower($budget->getType()) === strtolower(self::BUDGET_TYPE_DAILY);
    }

    /**
     * @param $budget
     * @return bool
     */
    public static function isMonthly($budget)
    {
        return strtolower($budget->getType()) === strtolower(self::BUDGET_TYPE_MONTHLY);
    }

    /**
     * @param $budget
     * @return bool
     */
    public static function isQuarterly($budget)
    {
        return strtolower($budget->getType()) === strtolower(self::BUDGET_TYPE_QUARTERLY);
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $startDate
     * @return bool
     */
    public static function isValidStartDate($startDate)
    {
        $datePattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';

        return (bool) preg_match($datePattern, $startDate);
    }

    /**
     * @param string $duration
     * @return bool
     */
    public static function isValidDuration($duration)
    {
        return $duration && (ctype_digit($duration) || is_int($duration));
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isValidType($type)
    {
        return $type && BudgetTypes::isValidBudgetType($type);
    }

    /**
     * @param $budget
     * @return bool
     */
    public static function isYearly($budget)
    {
        return strtolower($budget->getType()) === strtolower(self::BUDGET_TYPE_YEARLY);
    }

    /**
     * @param string $startDate
     * @param string $duration
     * @param string $budgetType
     * @return false|string
     */
    public function calculateSavedEndDate($startDate, $duration, $budgetType)
    {
        if (!$startDate || !$duration || !$budgetType) {
            return '';
        }
        try {
            $dateString = str_replace('-', '/', $startDate);
            $date = strtotime($dateString);
            $date = strtotime($this->getStepDuration($budgetType, $duration), $date);
            return date('Y-m-d', $date);
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
        }
    }

    /**
     * @param string $budgetType
     * @param string $duration
     * @return string
     */
    private function getStepDuration($budgetType, $duration)
    {
        $type = strtolower($budgetType);
        switch ($type) {
            case strtolower(self::BUDGET_TYPE_DAILY):
                $stepDuration = "+$duration day";
                break;
            case strtolower(self::BUDGET_TYPE_MONTHLY):
                $stepDuration = "+$duration month";
                break;
            case strtolower(self::BUDGET_TYPE_QUARTERLY):
                $duration = 3 * $duration;
                $stepDuration = "+$duration month";
                break;
            case strtolower(self::BUDGET_TYPE_YEARLY):
                $stepDuration = "+$duration year";
                break;
            default:
                $stepDuration = '';
        }

        return $stepDuration;
    }
}