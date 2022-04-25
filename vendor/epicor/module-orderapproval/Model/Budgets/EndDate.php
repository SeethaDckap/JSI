<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use Epicor\OrderApproval\Api\CalculateBudgetEndDateInterface;
use Epicor\OrderApproval\Model\ErpAccountBudget;
use Epicor\OrderApproval\Model\Groups\Budget as GroupBudget;
use Magento\Framework\Webapi\Rest\Request;
use Epicor\OrderApproval\Model\Budgets\BudgetTypes;
use DateTime;

class EndDate implements CalculateBudgetEndDateInterface
{
    const DATE_FORMAT_GMT = 'Y-m-d H:i:s';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var BudgetTypes
     */
    private $budgetTypes;

    /**
     * EndDate constructor.
     * @param Request $request
     * @param BudgetTypes $budgetTypes
     */
    public function __construct(
        Request $request,
        BudgetTypes $budgetTypes
    ) {
        $this->request = $request;
        $this->budgetTypes = $budgetTypes;
    }

    /**
     * @param null $start
     * @param null $duration
     * @param null $type
     * @return false|mixed|string
     */
    public function calculateBudgetEndDate($start = null, $duration = null, $type = null)
    {
        if (!$start && !$duration && !$type) {
            $start = $this->request->getParam('start');
            $duration = $this->request->getParam('duration');
            $type = ucfirst($this->request->getParam('type'));
        }


        return $this->budgetTypes->calculateSavedEndDate($start, $duration, $type);
    }

    /**
     * @param string $endDate
     * @return bool
     * @throws \Exception
     */
    public static function isExpired($endDate)
    {
        $now = new DateTime();
        $end = new DateTime($endDate);

        return $end < $now;
    }

    /**
     * @param string $startDate
     * @return bool
     * @throws \Exception
     */
    public static function isStarted($startDate)
    {
        $now = new DateTime();
        $start = new DateTime($startDate);

        return $start <= $now;
    }

    /**
     * @return string
     * @param ErpAccountBudget|GroupBudget $budget
     */
    public function getEndDate($budget)
    {
        return $this->budgetTypes->calculateSavedEndDate(
            $budget->getStartDate(),
            $budget->getDuration(),
            $budget->getType()
        );
    }

}
